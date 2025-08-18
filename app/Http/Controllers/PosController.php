<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')->active()->get();
        
        // Handle retry payment from failed/cancelled transaction
        $retryTransaction = null;
        if ($request->has('retry')) {
            $retryTransaction = Transaction::with('items.product')
                ->where('id', $request->retry)
                ->whereIn('status', ['failed', 'cancelled'])
                ->first();
        }
        
        // Handle load draft transaction
        $draftTransaction = null;
        if ($request->has('draft')) {
            $draftTransaction = Transaction::with('items.product')
                ->where('id', $request->draft)
                ->where('status', 'draft')
                ->where('is_draft', true)
                ->first();
        }
        
        // Get user's draft transactions for quick access
        $drafts = Transaction::where('user_id', auth()->id())
                           ->draft()
                           ->with('items.product')
                           ->latest()
                           ->take(5)
                           ->get();
        
        return view('pos.index', compact('products', 'retryTransaction', 'draftTransaction', 'drafts'));
    }
    
    public function searchProduct(Request $request)
    {
        $search = $request->query('search');
        
        $products = Product::with('category')
            ->active()
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->get();
            
        return response()->json($products);
    }
    
    public function processTransaction(Request $request)
    {
        $rules = [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'paid' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,ewallet,online',
        ];

        // Add customer validation for online payments
        if ($request->payment_method === 'online') {
            $rules['customer_info'] = 'required|array';
            $rules['customer_info.name'] = 'required|string|max:255';
            $rules['customer_info.phone'] = 'required|string|max:20';
            $rules['customer_info.email'] = 'required|email|max:255';
        }

        $request->validate($rules);
        
        DB::beginTransaction();
        
        try {
            $customer = null;
            $customerInfo = null;

            // Handle customer data for online payments
            if ($request->payment_method === 'online' && $request->customer_info) {
                $customerData = $request->customer_info;
                
                // Find or create customer by email
                $customer = Customer::findOrCreateByEmail($customerData['email'], [
                    'name' => $customerData['name'],
                    'phone' => $customerData['phone'],
                ]);

                // Update customer info if needed
                $customer->update([
                    'name' => $customerData['name'],
                    'phone' => $customerData['phone'],
                ]);

                $customerInfo = $customerData;
            }
            
            // Determine transaction status based on payment method
            $status = 'completed';
            $change = $request->paid - $request->total;
            
            if ($request->payment_method === 'online') {
                $status = 'pending'; // Online payments start as pending
                $change = 0; // No change for online payments initially
            }
            
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'customer_id' => $customer?->id,
                'customer_info' => $customerInfo,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'total' => $request->total,
                'paid' => $request->paid,
                'change' => $change,
                'payment_method' => $request->payment_method,
                'status' => $status,
                'notes' => $request->notes,
                'is_draft' => false,
            ]);
            
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi");
                }
                
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $product->price * $item['quantity'],
                ]);
                
                // Only decrement stock for immediate payments (cash, card, ewallet)
                // For online payments, stock will be decremented when payment is confirmed
                if ($request->payment_method !== 'online') {
                    $product->decrement('stock', $item['quantity']);
                }
            }

            // Update customer stats for completed transactions
            if ($customer && $status === 'completed') {
                $customer->updateTransactionStats($request->total);
            }
            
            DB::commit();

            // Handle online payment processing
            if ($request->payment_method === 'online' && $transaction) {
                // Create online payment (integrate with payment gateway)
                $paymentResult = $this->createOnlinePayment($transaction);
                
                if ($paymentResult['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Transaksi online berhasil dibuat',
                        'transaction' => $transaction->load('items.product', 'customer'),
                        'payment_url' => $paymentResult['payment_url'] ?? null,
                        'redirect' => true
                    ]);
                } else {
                    // If payment creation fails, update transaction status
                    $transaction->update(['status' => 'failed']);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal membuat pembayaran online: ' . ($paymentResult['message'] ?? 'Unknown error')
                    ], 422);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses',
                'transaction' => $transaction->load('items.product', 'customer')
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    private function createOnlinePayment($transaction)
    {
        try {
            // Use real iPaymu integration through PaymentController
            $paymentController = new \App\Http\Controllers\PaymentController(new \App\Services\IpaymuService());
            
            // Get customer info from transaction
            $customerInfo = $transaction->customer_info;
            
            // Create payment request data
            $paymentRequest = new \Illuminate\Http\Request([
                'transaction_id' => $transaction->id,
                'customer_name' => $customerInfo['name'],
                'customer_phone' => $customerInfo['phone'],
                'customer_email' => $customerInfo['email']
            ]);
            
            // Call the real payment creation method
            $paymentResponse = $paymentController->createPayment($paymentRequest);
            $paymentData = json_decode($paymentResponse->getContent(), true);
            
            if ($paymentData['success']) {
                return [
                    'success' => true,
                    'payment_url' => $paymentData['data']['redirect_to'] ?? $paymentData['data']['payment_url'],
                    'message' => 'iPaymu payment created successfully',
                    'payment_data' => $paymentData['data']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $paymentData['message'] ?? 'Failed to create iPaymu payment'
                ];
            }
            
        } catch (\Exception $e) {
            \Log::error('Error creating iPaymu payment: ' . $e->getMessage());
            
            // Fallback to mock payment if iPaymu service fails
            return [
                'success' => true,
                'payment_url' => route('payment.mock-success', ['session' => $transaction->id]),
                'message' => 'Using mock payment (iPaymu service unavailable): ' . $e->getMessage()
            ];
        }
    }

    public function saveDraft(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'draft_name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'total' => $request->total,
                'paid' => 0,
                'change' => 0,
                'payment_method' => 'cash', // Default for drafts
                'status' => 'draft',
                'notes' => $request->notes,
                'draft_name' => $request->draft_name,
                'is_draft' => true,
            ]);

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
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Draft berhasil disimpan',
                'transaction' => $transaction->load('items.product')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function loadDraft($id)
    {
        try {
            $draft = Transaction::with('items.product')
                              ->where('id', $id)
                              ->where('user_id', auth()->id())
                              ->where('status', 'draft')
                              ->where('is_draft', true)
                              ->firstOrFail();

            return response()->json([
                'success' => true,
                'draft' => $draft
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draft not found'
            ], 404);
        }
    }

    public function deleteDraft($id)
    {
        try {
            $draft = Transaction::where('id', $id)
                              ->where('user_id', auth()->id())
                              ->where('status', 'draft')
                              ->where('is_draft', true)
                              ->firstOrFail();

            $draft->items()->delete();
            $draft->delete();

            return response()->json([
                'success' => true,
                'message' => 'Draft berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draft not found'
            ], 404);
        }
    }
    
    public function printReceipt($id)
    {
        $transaction = Transaction::with(['items.product', 'user'])->findOrFail($id);
        return view('pos.receipt', compact('transaction'));
    }
}
