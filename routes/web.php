<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('store.index');
});

// Online Store Routes (public access)
Route::prefix('store')->name('store.')->group(function () {
    Route::get('/', [StoreController::class, 'index'])->name('index');
    Route::get('/product/{product}', [StoreController::class, 'show'])->name('product');
    Route::get('/cart', [StoreController::class, 'cart'])->name('cart');
    Route::post('/add-to-cart/{product}', [StoreController::class, 'addToCart'])->name('add-to-cart');
    Route::post('/update-cart', [StoreController::class, 'updateCart'])->name('update-cart');
    Route::post('/remove-from-cart', [StoreController::class, 'removeFromCart'])->name('remove-from-cart');
    Route::get('/checkout', [StoreController::class, 'checkout'])->name('checkout');
    Route::post('/process-order', [StoreController::class, 'processOrder'])->name('process-order');
    Route::get('/payment/{transaction}', [StoreController::class, 'redirectToPayment'])->name('payment');
    Route::get('/order-success/{transaction}', [StoreController::class, 'orderSuccess'])->name('order-success');
});

// Payment routes (no auth required)
Route::post('payment/callback', [App\Http\Controllers\PaymentController::class, 'handleCallback'])->name('payment.callback');
Route::get('payment/success', [App\Http\Controllers\PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('payment/cancel', [App\Http\Controllers\PaymentController::class, 'paymentCancel'])->name('payment.cancel');
Route::get('payment/failed', [App\Http\Controllers\PaymentController::class, 'paymentFailed'])->name('payment.failed');
Route::get('payment/mock-success', function() {
    return view('payment.mock-success', [
        'session' => request('session'),
        'message' => 'Mock Payment Success - iPaymu API was unavailable during testing'
    ]);
})->name('payment.mock-success');

// Offline fallback page (no auth)
Route::get('/offline', fn() => view('offline'))->name('offline');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::middleware(['role:admin,supervisor'])->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::get('products/lookup-barcode', [ProductController::class, 'lookupBarcode'])->name('products.lookup-barcode');
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
        Route::get('products/import-template', [ProductController::class, 'importTemplate'])->name('products.import-template');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::resource('products', ProductController::class);
        
        // Barcode routes
        Route::post('products/{product}/generate-barcode', [ProductController::class, 'generateBarcode'])->name('products.generate-barcode');
        Route::post('products/{product}/regenerate-barcode', [ProductController::class, 'regenerateBarcode'])->name('products.regenerate-barcode');
        Route::get('products/{product}/print-barcode', [ProductController::class, 'printBarcode'])->name('products.print-barcode');
        Route::get('products/{product}/barcode-image', [ProductController::class, 'getBarcodeImage'])->name('products.barcode-image');
        
        Route::resource('customers', App\Http\Controllers\CustomerController::class);
        Route::patch('customers/{customer}/toggle-status', [App\Http\Controllers\CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
        Route::get('customers/export/csv', [App\Http\Controllers\CustomerController::class, 'export'])->name('customers.export');
        
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/products', [ReportController::class, 'products'])->name('reports.products');
        Route::get('reports/export/sales', [ReportController::class, 'exportSales'])->name('reports.export.sales');
        Route::get('reports/export/products', [ReportController::class, 'exportProducts'])->name('reports.export.products');
    });
    
    Route::middleware(['role:admin,supervisor,kasir'])->group(function () {
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('pos/search', [PosController::class, 'searchProduct'])->name('pos.search');
        Route::post('pos/search-barcode', [PosController::class, 'searchByBarcode'])->name('pos.search-barcode');
        Route::post('pos/transaction', [PosController::class, 'processTransaction'])->name('pos.transaction');
        Route::get('pos/receipt/{id}', [PosController::class, 'printReceipt'])->name('pos.receipt');
        Route::get('pos/receipt/{id}/escpos', [PosController::class, 'escposReceipt'])->name('pos.receipt.escpos');

        // Draft functionality
        Route::post('pos/draft/save', [PosController::class, 'saveDraft'])->name('pos.draft.save');
        Route::get('pos/draft/{id}', [PosController::class, 'loadDraft'])->name('pos.draft.load');
        Route::delete('pos/draft/{id}', [PosController::class, 'deleteDraft'])->name('pos.draft.delete');

        // Customer search for POS
        Route::get('pos/customer-search', [PosController::class, 'searchCustomer'])->name('pos.customer-search');

        // Offline sync API
        Route::post('api/pos/sync-transaction', [App\Http\Controllers\Api\SyncController::class, 'syncTransaction'])->name('api.pos.sync-transaction');
        Route::get('api/pos/products', [App\Http\Controllers\Api\SyncController::class, 'products'])->name('api.pos.products');
        Route::get('api/pos/transactions', [App\Http\Controllers\Api\SyncController::class, 'transactions'])->name('api.pos.transactions');
        Route::get('api/csrf-token', [App\Http\Controllers\Api\SyncController::class, 'csrfToken'])->name('api.csrf-token');
        Route::post('api/pos/sync-product', [App\Http\Controllers\Api\SyncController::class, 'syncProductAction'])->name('api.pos.sync-product');

        // Bon/Hutang management
        Route::get('bon', [App\Http\Controllers\BonController::class, 'index'])->name('bon.index');
        Route::post('bon/{transaction}/pay', [App\Http\Controllers\BonController::class, 'markAsPaid'])->name('bon.pay');

        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        
        // Payment Gateway Routes
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('channels', [App\Http\Controllers\PaymentController::class, 'getPaymentChannels'])->name('channels');
            Route::post('create', [App\Http\Controllers\PaymentController::class, 'createPayment'])->name('create');
            Route::post('status', [App\Http\Controllers\PaymentController::class, 'checkPaymentStatus'])->name('status');
        });
        
        // iPaymu Dashboard Routes
        Route::prefix('ipaymu')->name('ipaymu.')->group(function () {
            Route::get('transactions', [App\Http\Controllers\PaymentController::class, 'ipaymuTransactions'])->name('transactions');
            Route::get('transactions/{transaction}', [App\Http\Controllers\PaymentController::class, 'showIpaymuTransaction'])->name('transaction.show');
        });
    });
    
    Route::middleware(['role:admin'])->group(function () {
        Route::put('transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::post('transactions/{transaction}/retry', [TransactionController::class, 'retry'])->name('transactions.retry');
        Route::post('transactions/{transaction}/refund', [TransactionController::class, 'createRefund'])->name('transactions.refund');
        Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
        
        // User Management - Admin only
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        // Settings Management - Admin only
        Route::get('settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
        Route::delete('settings/remove-logo', [App\Http\Controllers\SettingController::class, 'removeLogo'])->name('settings.remove-logo');
        Route::post('settings/test-ipaymu', [App\Http\Controllers\SettingController::class, 'testIpaymu'])->name('settings.test-ipaymu');

        // Git Pull - Admin only
        Route::get('git-pull', [App\Http\Controllers\GitPullController::class, 'index'])->name('git-pull.index');
        Route::post('git-pull/pull', [App\Http\Controllers\GitPullController::class, 'pull'])->name('git-pull.pull');
        Route::post('git-pull/settings', [App\Http\Controllers\GitPullController::class, 'saveSettings'])->name('git-pull.settings');
        Route::post('git-pull/post-action', [App\Http\Controllers\GitPullController::class, 'postAction'])->name('git-pull.post-action');
    });
});

require __DIR__.'/auth.php';
