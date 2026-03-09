<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    /**
     * Sync a single offline transaction.
     */
    public function syncTransaction(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paid' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,ewallet,bon',
            '_offline_id' => 'required|string|max:36',
            '_offline_created_at' => 'nullable|string',
        ]);

        // Idempotency: check if already synced
        $existing = Transaction::where('offline_id', $request->_offline_id)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi sudah disinkronkan sebelumnya',
                'transaction' => $existing->load('items.product'),
                'duplicate' => true,
            ]);
        }

        DB::beginTransaction();

        try {
            $customer = null;
            $customerInfo = null;

            // Handle bon customer
            if ($request->payment_method === 'bon' && $request->customer_info) {
                $customerData = $request->customer_info;
                $customerInfo = $customerData;

                if (!empty($customerData['phone'])) {
                    $customer = Customer::findOrCreateByPhone($customerData['phone'], [
                        'name' => $customerData['name'],
                        'address' => $customerData['address'] ?? null,
                    ]);
                }
            }

            $status = ($request->payment_method === 'bon') ? 'pending' : 'completed';
            $change = ($request->payment_method === 'bon') ? 0 : max(0, $request->paid - $request->total);

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'customer_id' => $customer?->id,
                'customer_info' => $customerInfo,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'total' => $request->total,
                'paid' => ($request->payment_method === 'bon') ? 0 : $request->paid,
                'change' => $change,
                'payment_method' => $request->payment_method,
                'status' => $status,
                'notes' => 'Synced from offline',
                'is_draft' => false,
                'offline_id' => $request->_offline_id,
            ]);

            // Override created_at if offline timestamp provided
            if ($request->_offline_created_at) {
                $transaction->update(['created_at' => $request->_offline_created_at]);
            }

            $stockWarnings = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $product->price * $item['quantity'],
                ]);

                // Allow negative stock (transaction already happened physically)
                if ($product->stock < $item['quantity']) {
                    $stockWarnings[] = "{$product->name}: stok kurang (tersisa {$product->stock}, diminta {$item['quantity']})";
                }
                $product->decrement('stock', $item['quantity']);
            }

            if ($customer && $status === 'completed') {
                $customer->updateTransactionStats($request->total);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi offline berhasil disinkronkan',
                'transaction' => $transaction->load('items.product'),
                'stock_warnings' => $stockWarnings,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Return fresh CSRF token.
     */
    public function csrfToken(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }

    /**
     * Sync a single offline product action (create/update/delete).
     */
    public function syncProductAction(Request $request): JsonResponse
    {
        $request->validate([
            'action_type' => 'required|in:create,update,delete',
            'action_id' => 'required|string|max:36',
            'product_id' => 'nullable|integer',
            'data' => 'nullable|array',
        ]);

        $actionType = $request->action_type;
        $data = $request->data ?? [];

        try {
            if ($actionType === 'create') {
                $validated = validator($data, [
                    'name' => 'required|string|max:255',
                    'sku' => 'required|string|max:255|unique:products,sku',
                    'barcode' => 'nullable|string|max:255',
                    'description' => 'nullable|string',
                    'price' => 'required|numeric|min:0',
                    'cost' => 'required|numeric|min:0',
                    'stock' => 'required|integer|min:0',
                    'min_stock' => 'required|integer|min:0',
                    'category_id' => 'required|exists:categories,id',
                    'is_active' => 'nullable',
                ])->validate();

                $validated['is_active'] = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

                $product = Product::create($validated);

                return response()->json([
                    'success' => true,
                    'message' => "Produk '{$product->name}' berhasil dibuat",
                    'product' => $product,
                ]);

            } elseif ($actionType === 'update') {
                $product = Product::findOrFail($request->product_id);

                $validated = validator($data, [
                    'name' => 'sometimes|string|max:255',
                    'sku' => 'sometimes|string|max:255|unique:products,sku,' . $product->id,
                    'barcode' => 'nullable|string|max:255',
                    'description' => 'nullable|string',
                    'price' => 'sometimes|numeric|min:0',
                    'cost' => 'sometimes|numeric|min:0',
                    'stock' => 'sometimes|integer|min:0',
                    'min_stock' => 'sometimes|integer|min:0',
                    'category_id' => 'sometimes|exists:categories,id',
                    'is_active' => 'nullable',
                ])->validate();

                if (isset($data['is_active'])) {
                    $validated['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
                }

                $product->update($validated);

                return response()->json([
                    'success' => true,
                    'message' => "Produk '{$product->name}' berhasil diperbarui",
                    'product' => $product->fresh(),
                ]);

            } elseif ($actionType === 'delete') {
                $product = Product::findOrFail($request->product_id);
                $name = $product->name;

                // Check if product has transactions
                $hasTransactions = TransactionItem::where('product_id', $product->id)->exists();
                if ($hasTransactions) {
                    // Soft-deactivate instead of delete
                    $product->update(['is_active' => false]);
                    return response()->json([
                        'success' => true,
                        'message' => "Produk '{$name}' dinonaktifkan (memiliki riwayat transaksi)",
                        'deactivated' => true,
                    ]);
                }

                $product->delete();

                return response()->json([
                    'success' => true,
                    'message' => "Produk '{$name}' berhasil dihapus",
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Unknown action'], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan (mungkin sudah dihapus)',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return recent transactions for offline cache.
     */
    public function transactions(): JsonResponse
    {
        $query = Transaction::with(['user', 'items.product']);

        // Non-admin users only see their own
        if (!auth()->user()->isAdmin() && !auth()->user()->isSupervisor()) {
            $query->where('user_id', auth()->id());
        }

        $transactions = $query->latest()
            ->limit(100)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'transaction_number' => $t->transaction_number,
                'user_name' => $t->user->name ?? '-',
                'subtotal' => $t->subtotal,
                'discount' => $t->discount,
                'tax' => $t->tax,
                'total' => $t->total,
                'paid' => $t->paid,
                'change' => $t->change,
                'payment_method' => $t->payment_method,
                'status' => $t->status,
                'notes' => $t->notes,
                'customer_info' => $t->customer_info,
                'bon_paid_at' => $t->bon_paid_at?->toISOString(),
                'bon_paid_amount' => $t->bon_paid_amount,
                'created_at' => $t->created_at->toISOString(),
                'items_count' => $t->items->count(),
                'items' => $t->items->map(fn($i) => [
                    'product_name' => $i->product_name,
                    'product_price' => $i->product_price,
                    'quantity' => $i->quantity,
                    'subtotal' => $i->subtotal,
                    'product_sku' => $i->product->sku ?? '',
                    'product_image' => $i->product->image ?? '',
                ]),
            ]);

        return response()->json($transactions);
    }

    /**
     * Return all active products for IndexedDB sync.
     */
    public function products(): JsonResponse
    {
        $products = Product::with('category')
            ->active()
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'price' => $p->price,
                'cost' => $p->cost,
                'stock' => $p->stock,
                'category_id' => $p->category_id,
                'category_name' => $p->category->name ?? null,
                'image' => $p->image,
                'is_active' => $p->is_active,
            ]);

        return response()->json($products);
    }
}
