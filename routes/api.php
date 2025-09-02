<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Payment Callback API - No authentication required for external webhooks
Route::post('/payment/callback', [PaymentController::class, 'handleCallback'])->name('api.payment.callback');

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'service' => 'POS API'
    ]);
});

// Barcode API endpoints
Route::post('/products/search-barcode', [App\Http\Controllers\ProductController::class, 'searchByBarcode'])->name('api.products.search-barcode');

// Dashboard stats for notifications
Route::middleware('auth')->get('/dashboard-stats', [App\Http\Controllers\DashboardController::class, 'getStats'])->name('api.dashboard-stats');

// API endpoint to get iPaymu transactions data
Route::get('/ipaymu/transactions', function () {
    $transactions = \App\Models\Transaction::whereNotNull('ipaymu_transaction_id')
                              ->with(['customer'])
                              ->orderBy('created_at', 'desc')
                              ->limit(10)
                              ->get()
                              ->map(function ($transaction) {
                                  return [
                                      'id' => $transaction->id,
                                      'transaction_number' => $transaction->transaction_number,
                                      'ipaymu_transaction_id' => $transaction->ipaymu_transaction_id,
                                      'ipaymu_session_id' => $transaction->ipaymu_session_id,
                                      'status' => $transaction->status,
                                      'ipaymu_status' => $transaction->ipaymu_status,
                                      'total' => $transaction->total,
                                      'paid' => $transaction->paid,
                                      'ipaymu_fee' => $transaction->ipaymu_fee,
                                      'ipaymu_payment_method' => $transaction->ipaymu_payment_method,
                                      'ipaymu_payment_channel' => $transaction->ipaymu_payment_channel,
                                      'ipaymu_paid_at' => $transaction->ipaymu_paid_at,
                                      'created_at' => $transaction->created_at,
                                      'customer' => $transaction->customer ? [
                                          'name' => $transaction->customer->name,
                                          'email' => $transaction->customer->email,
                                          'phone' => $transaction->customer->phone,
                                      ] : null,
                                  ];
                              });
    
    return response()->json([
        'success' => true,
        'data' => $transactions,
        'total' => $transactions->count()
    ]);
})->name('api.ipaymu.transactions');