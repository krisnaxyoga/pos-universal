<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Customer;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->get();
        
        $query = Product::with('category')->active();
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Search products
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Sort products
        $sortBy = $request->get('sort', 'name');
        $sortOrder = $request->get('order', 'asc');
        
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }
        
        $products = $query->paginate(12)->withQueryString();
        
        // Get cart count
        $cartCount = $this->getCartCount();
        
        return view('store.index', compact('products', 'categories', 'cartCount'));
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }
        
        $relatedProducts = Product::with('category')
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();
        
        $cartCount = $this->getCartCount();
        
        return view('store.product', compact('product', 'relatedProducts', 'cartCount'));
    }

    public function addToCart(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);
        
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak tersedia'
            ]);
        }
        
        $quantity = $request->quantity;
        
        // Check stock availability
        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi. Tersedia: ' . $product->stock
            ]);
        }
        
        // Get current cart from session
        $cart = Session::get('cart', []);
        $productId = $product->id;
        
        // Check if product already in cart
        if (isset($cart[$productId])) {
            $newQuantity = $cart[$productId]['quantity'] + $quantity;
            
            // Check total quantity against stock
            if ($product->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total quantity melebihi stok. Tersedia: ' . $product->stock . ', di cart: ' . $cart[$productId]['quantity']
                ]);
            }
            
            $cart[$productId]['quantity'] = $newQuantity;
            $cart[$productId]['subtotal'] = $newQuantity * $product->price;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $quantity * $product->price,
                'image' => $product->image
            ];
        }
        
        Session::put('cart', $cart);
        
        return response()->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang',
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function cart()
    {
        $cart = Session::get('cart', []);
        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $item) {
            // Verify product still exists and active
            $product = Product::find($item['id']);
            if ($product && $product->is_active) {
                // Update price in case it changed
                $item['price'] = $product->price;
                $item['subtotal'] = $item['quantity'] * $product->price;
                $item['stock'] = $product->stock;
                $cartItems[] = $item;
                $total += $item['subtotal'];
            }
        }
        
        // Update cart in session with current prices
        $updatedCart = [];
        foreach ($cartItems as $item) {
            $updatedCart[$item['id']] = $item;
        }
        Session::put('cart', $updatedCart);
        
        $cartCount = count($cartItems);
        
        return view('store.cart', compact('cartItems', 'total', 'cartCount'));
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0'
        ]);
        
        $cart = Session::get('cart', []);
        $productId = $request->product_id;
        $quantity = $request->quantity;
        
        if ($quantity == 0) {
            // Remove item from cart
            unset($cart[$productId]);
        } else {
            // Check stock
            $product = Product::find($productId);
            if ($product->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Tersedia: ' . $product->stock
                ]);
            }
            
            // Update quantity
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $quantity;
                $cart[$productId]['subtotal'] = $quantity * $cart[$productId]['price'];
            }
        }
        
        Session::put('cart', $cart);
        
        return response()->json([
            'success' => true,
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $cart = Session::get('cart', []);
        unset($cart[$request->product_id]);
        Session::put('cart', $cart);
        
        return response()->json([
            'success' => true,
            'message' => 'Produk dihapus dari keranjang',
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function checkout()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('store.index')->with('error', 'Keranjang kosong');
        }
        
        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $item) {
            $product = Product::find($item['id']);
            if ($product && $product->is_active && $product->stock >= $item['quantity']) {
                $item['price'] = $product->price;
                $item['subtotal'] = $item['quantity'] * $product->price;
                $cartItems[] = $item;
                $total += $item['subtotal'];
            }
        }
        
        if (empty($cartItems)) {
            Session::forget('cart');
            return redirect()->route('store.index')->with('error', 'Produk dalam keranjang tidak tersedia');
        }
        
        $cartCount = count($cartItems);
        
        return view('store.checkout', compact('cartItems', 'total', 'cartCount'));
    }

    public function processOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
        ]);
        
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('store.index')->with('error', 'Keranjang kosong');
        }
        
        DB::beginTransaction();
        
        try {
            // Find or create customer
            $customer = Customer::findOrCreateByEmail($request->customer_email, [
                'name' => $request->customer_name,
                'phone' => $request->customer_phone,
                'address' => $request->customer_address,
            ]);
            
            // Update customer info
            $customer->update([
                'name' => $request->customer_name,
                'phone' => $request->customer_phone,
                'address' => $request->customer_address,
            ]);
            
            $total = 0;
            $cartItems = [];
            
            // Verify stock and calculate total
            foreach ($cart as $item) {
                $product = Product::find($item['id']);
                
                if (!$product || !$product->is_active) {
                    throw new \Exception("Produk {$item['name']} tidak tersedia");
                }
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi. Tersedia: {$product->stock}");
                }
                
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $product->price * $item['quantity']
                ];
                
                $total += $product->price * $item['quantity'];
            }
            
            // Create transaction
            $transaction = Transaction::create([
                'user_id' => 1, // System user for online orders
                'customer_id' => $customer->id,
                'customer_info' => [
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'phone' => $request->customer_phone,
                    'address' => $request->customer_address,
                ],
                'subtotal' => $total,
                'discount' => 0,
                'tax' => 0,
                'total' => $total,
                'paid' => 0, // All online store orders start as unpaid
                'change' => 0,
                'payment_method' => 'online', // Always use online for iPaymu
                'status' => 'pending', // All online store orders start as pending payment
                'notes' => 'Online Store Order',
                'is_draft' => false,
            ]);
            
            // Create transaction items and reduce stock
            foreach ($cartItems as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
                
                // Stock will be reduced when payment is confirmed
                // For now, reserve the stock by not reducing immediately
            }
            
            // Update customer stats for completed transactions
            if ($transaction->status === 'completed') {
                $customer->updateTransactionStats($total);
            }
            
            DB::commit();
            
            // Clear cart
            Session::forget('cart');
            
            // Redirect to payment page
            return redirect()->route('store.payment', $transaction->id);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    public function orderSuccess($transactionId)
    {
        $transaction = Transaction::with(['items.product', 'customer'])
            ->where('id', $transactionId)
            ->first();
        
        if (!$transaction) {
            return redirect()->route('store.index');
        }
        
        return view('store.order-success', compact('transaction'));
    }

    public function redirectToPayment(Transaction $transaction)
    {
        // Verify transaction exists and belongs to store
        if (!$transaction || $transaction->payment_method !== 'online') {
            return redirect()->route('store.index')->with('error', 'Transaksi tidak valid');
        }

        // Check if already paid
        if ($transaction->status === 'completed') {
            return redirect()->route('store.order-success', $transaction->id);
        }

        try {
            // Create payment request using existing PaymentController
            $paymentController = app(PaymentController::class);
            
            // Prepare request data for PaymentController
            $customerInfo = $transaction->customer_info;
            $requestData = [
                'transaction_id' => $transaction->id,
                'customer_name' => $customerInfo['name'] ?? $transaction->customer->name ?? '',
                'customer_phone' => $customerInfo['phone'] ?? $transaction->customer->phone ?? '',
                'customer_email' => $customerInfo['email'] ?? $transaction->customer->email ?? '',
            ];

            // Create request object
            $request = Request::create('/payment/create', 'POST', $requestData);
            $request->setLaravelSession(request()->session());
            
            // Call PaymentController's createPayment method
            $response = $paymentController->createPayment($request);
            $responseData = $response->getData(true);

            if ($responseData['success'] && isset($responseData['data']['redirect_to'])) {
                // Redirect to iPaymu payment URL
                return redirect()->away($responseData['data']['redirect_to']);
            } else {
                // Payment creation failed, redirect back with error
                return redirect()->route('store.checkout')->with('error', 'Gagal membuat pembayaran: ' . ($responseData['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            \Log::error('Store payment redirect failed: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->route('store.checkout')->with('error', 'Terjadi kesalahan saat memproses pembayaran');
        }
    }

    private function getCartCount()
    {
        $cart = Session::get('cart', []);
        return array_sum(array_column($cart, 'quantity'));
    }
}