<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\IpaymuService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private IpaymuService $ipaymuService;

    public function __construct(IpaymuService $ipaymuService)
    {
        try {
            $this->ipaymuService = $ipaymuService;
        } catch (\Exception $e) {
            Log::error('Failed to initialize IpaymuService: ' . $e->getMessage());
            // Fallback: create service manually if dependency injection fails
            $this->ipaymuService = new IpaymuService();
        }
    }

    /**
     * Get available payment channels from iPaymu
     */
    public function getPaymentChannels(): JsonResponse
    {
        try {
            $result = $this->ipaymuService->getPaymentChannels();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch payment channels',
                    'error' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error fetching payment channels: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment channels',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create payment transaction with iPaymu
     */
    public function createPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:transactions,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Log::info('Creating payment for transaction', [
                'transaction_id' => $request->transaction_id,
                'customer' => [
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'email' => $request->customer_email
                ]
            ]);

            DB::beginTransaction();

            // Get transaction data
            $transaction = Transaction::with('items.product')->findOrFail($request->transaction_id);

            // Check if transaction is already paid or processing
            if (in_array($transaction->status, ['completed', 'processing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction is already processed'
                ], 400);
            }

            // Prepare payment data for iPaymu regular payment
            $products = [];
            $quantities = [];
            $prices = [];

            foreach ($transaction->items as $item) {
                $products[] = $item->product_name;
                $quantities[] = $item->quantity;
                $prices[] = $item->product_price;
            }

            $paymentData = [
                'product' => $products,
                'qty' => $quantities,
                'price' => $prices,
                'referenceId' => $transaction->transaction_number,
                'buyerName' => $request->customer_name,
                'buyerPhone' => $request->customer_phone,
                'buyerEmail' => $request->customer_email,
                'returnUrl' => route('payment.success'),
                'cancelUrl' => route('payment.cancel'),
            ];

            // Create payment with iPaymu
            Log::info('Calling iPaymu createPayment', ['data' => $paymentData]);

            try {
                $paymentResult = $this->ipaymuService->createPayment($paymentData);
                Log::info('iPaymu createPayment result', ['result' => $paymentResult]);
            } catch (\Exception $ipaymuException) {
                Log::error('iPaymu service error: ' . $ipaymuException->getMessage());
                throw new \Exception('Gagal menghubungi layanan pembayaran: ' . $ipaymuException->getMessage());
            }

            if (!$paymentResult['success']) {
                Log::error('iPaymu payment creation failed', ['result' => $paymentResult]);
                throw new \Exception($paymentResult['error'] ?? 'Failed to create payment');
            }

            $ipaymuData = $paymentResult['data']['Data'];

            // Update transaction with iPaymu data
            $transaction->update([
                'status' => 'processing',
                'payment_method' => 'online',
                'ipaymu_session_id' => $ipaymuData['SessionID'] ?? null,
                'ipaymu_reference_id' => $transaction->transaction_number,
                'ipaymu_amount' => $transaction->total,
                'ipaymu_fee' => $ipaymuData['Fee'] ?? 0,
                'ipaymu_payment_url' => $ipaymuData['Url'] ?? null,
                'ipaymu_expired_date' => $ipaymuData['Expired'] ?? null,
            ]);

            DB::commit();

            // Format response for frontend - redirect to payment URL
            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'session_id' => $ipaymuData['SessionID'] ?? null,
                    'payment_url' => $ipaymuData['Url'] ?? null,
                    'amount' => $transaction->total,
                    'fee' => $ipaymuData['Fee'] ?? 0,
                    'expired_date' => $ipaymuData['Expired'] ?? null,
                    'redirect_to' => $ipaymuData['Url'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating payment: ' . $e->getMessage(), [
                'transaction_id' => $request->transaction_id,
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment callback from iPaymu
     */
    public function handleCallback(Request $request): JsonResponse
    {
        try {
            Log::info('iPaymu Callback received', $request->all());

            // Get callback data directly from request
            $callbackData = $request->all();

            // Find transaction by reference ID
            $transaction = Transaction::where('ipaymu_reference_id', $callbackData['reference_id'])->first();

            if (!$transaction) {
                Log::warning('Transaction not found for callback', [
                    'reference_id' => $callbackData['reference_id'],
                    'callback_data' => $callbackData
                ]);
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            DB::beginTransaction();

            // Prepare update data with complete callback information
            $updateData = [
                'ipaymu_transaction_id' => $callbackData['trx_id'] ?? null,
                'ipaymu_session_id' => $callbackData['sid'] ?? null,
                'ipaymu_reference_id' => $callbackData['reference_id'] ?? null,
                'ipaymu_status' => $callbackData['status'] ?? null,
                'ipaymu_status_code' => $callbackData['status_code'] ?? null,
                'ipaymu_amount' => $callbackData['amount'] ?? null,
                'ipaymu_fee' => $callbackData['fee'] ?? null,
                'ipaymu_payment_method' => $callbackData['via'] ?? null,
                'ipaymu_payment_channel' => $callbackData['channel'] ?? null,
                'ipaymu_payment_code' => $callbackData['payment_no'] ?? null,
                'ipaymu_expired_date' => $callbackData['expired_at'] ?? null,
                'ipaymu_paid_at' => $callbackData['paid_at'] ?? null,
            ];

            // Handle different payment statuses
            switch ($callbackData['status']) {
                case 'berhasil':
                case 'success':
                    $updateData['status'] = 'completed';
                    $updateData['paid'] = $callbackData['amount'] ?? $transaction->total;
                    break;

                case 'pending':
                    $updateData['status'] = 'processing';
                    break;

                case 'gagal':
                case 'failed':
                case 'expired':
                    $updateData['status'] = 'failed';
                    break;
            }

            $transaction->update($updateData);

            // If payment is successful, decrement product stock
            if (in_array($callbackData['status'], ['berhasil', 'success'])) {
                foreach ($transaction->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->decrement('stock', $item->quantity);
                        Log::info('Stock decremented for successful payment', [
                            'transaction_id' => $transaction->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $item->quantity,
                            'remaining_stock' => $product->fresh()->stock
                        ]);
                    }
                }
            }

            // Log successful callback processing with transaction ID
            Log::info('Callback processed successfully', [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'ipaymu_trx_id' => $callbackData['trx_id'],
                'reference_id' => $callbackData['reference_id'],
                'status' => $callbackData['status'],
                'amount' => $callbackData['amount'],
                'via' => $callbackData['via'] ?? null,
                'channel' => $callbackData['channel'] ?? null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Callback processed successfully',
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'status' => $callbackData['status']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing callback: ' . $e->getMessage(), [
                'callback_data' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:transactions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transaction = Transaction::findOrFail($request->transaction_id);

            if (!$transaction->ipaymu_transaction_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No iPaymu transaction ID found'
                ], 400);
            }

            // Check status with iPaymu
            $statusResult = $this->ipaymuService->checkTransactionStatus($transaction->ipaymu_transaction_id);

            if (!$statusResult['success']) {
                throw new \Exception($statusResult['error'] ?? 'Failed to check status');
            }

            $statusData = $statusResult['data']['Data'];

            // Update local transaction status if changed
            if (isset($statusData['Status']) && $statusData['Status'] !== $transaction->ipaymu_status) {
                $transaction->update([
                    'ipaymu_status' => $statusData['Status'],
                    'status' => $this->mapIpaymuStatusToLocal($statusData['Status'])
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'local_status' => $transaction->status,
                    'ipaymu_status' => $statusData['Status'] ?? null,
                    'amount' => $transaction->total,
                    'paid_amount' => $transaction->paid ?? 0,
                    'payment_method' => $transaction->ipaymu_payment_method,
                    'payment_channel' => $transaction->ipaymu_payment_channel,
                    'payment_code' => $transaction->ipaymu_payment_code,
                    'expired_date' => $transaction->ipaymu_expired_date,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map iPaymu status to local status
     */
    private function mapIpaymuStatusToLocal(string $ipaymuStatus): string
    {
        return match($ipaymuStatus) {
            'berhasil', 'success' => 'completed',
            'pending' => 'processing',
            'gagal', 'failed', 'expired' => 'failed',
            default => 'processing'
        };
    }

    /**
     * Get payment instructions based on method and channel
     */
    private function getPaymentInstructions(string $method, string $channel, array $data): array
    {
        $instructions = [
            'method' => $method,
            'channel' => $channel,
            'steps' => []
        ];

        switch ($method) {
            case 'va':
                $instructions['steps'] = [
                    "Buka aplikasi mobile banking atau ATM " . strtoupper($channel),
                    "Pilih menu Transfer / Bayar",
                    "Pilih Virtual Account",
                    "Masukkan nomor Virtual Account: " . ($data['PaymentCode'] ?? ''),
                    "Masukkan jumlah yang harus dibayar",
                    "Konfirmasi pembayaran",
                    "Simpan bukti pembayaran"
                ];
                break;

            case 'qris':
                $instructions['steps'] = [
                    "Buka aplikasi e-wallet atau mobile banking yang mendukung QRIS",
                    "Pilih menu Scan QR atau QR Pay",
                    "Scan QR Code yang ditampilkan",
                    "Konfirmasi pembayaran",
                    "Simpan bukti pembayaran"
                ];
                break;

            case 'cstore':
                $instructions['steps'] = [
                    "Kunjungi " . ucfirst($channel) . " terdekat",
                    "Berikan kode pembayaran: " . ($data['PaymentCode'] ?? ''),
                    "Bayar sejumlah yang tertera",
                    "Simpan bukti pembayaran"
                ];
                break;

            default:
                $instructions['steps'] = [
                    "Ikuti instruksi pembayaran yang diberikan",
                    "Gunakan kode pembayaran: " . ($data['PaymentCode'] ?? ''),
                    "Bayar sesuai nominal yang tertera",
                    "Simpan bukti pembayaran"
                ];
        }

        return $instructions;
    }

    /**
     * Handle payment success redirect
     */
    public function paymentSuccess(Request $request)
    {
        try {
            $transactionId = $request->query('transaction_id');
            $sessionId = $request->query('session_id');

            Log::info('Payment success redirect', [
                'transaction_id' => $transactionId,
                'session_id' => $sessionId,
                'query_params' => $request->query()
            ]);

            if ($transactionId) {
                $transaction = Transaction::with('items.product')->find($transactionId);
                if ($transaction) {
                    // Update transaction status if still pending/processing
                    if (in_array($transaction->status, ['pending', 'processing'])) {
                        $transaction->update([
                            'status' => 'completed',
                            'paid' => $transaction->total,
                            'ipaymu_paid_at' => now()
                        ]);

                        // Decrement stock if not already done
                        foreach ($transaction->items as $item) {
                            if ($item->product && $transaction->payment_method === 'online') {
                                $item->product->decrement('stock', $item->quantity);
                            }
                        }
                    }

                    return view('payment.success', [
                        'transaction' => $transaction,
                        'message' => 'Payment completed successfully! Thank you for your purchase.'
                    ]);
                }
            }

            // Fallback for success without transaction ID
            return view('payment.success', [
                'message' => 'Payment completed successfully! Thank you for your purchase.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in payment success handler: ' . $e->getMessage());

            return view('payment.success', [
                'message' => 'Payment completed, but there was an issue loading transaction details.'
            ]);
        }
    }

    /**
     * Handle payment cancel redirect
     */
    public function paymentCancel(Request $request)
    {
        try {
            $transactionId = $request->query('transaction_id');
            $reason = $request->query('reason', 'cancelled');

            Log::info('Payment cancel redirect', [
                'transaction_id' => $transactionId,
                'reason' => $reason,
                'query_params' => $request->query()
            ]);

            if ($transactionId) {
                $transaction = Transaction::with('items.product')->find($transactionId);
                if ($transaction) {
                    // Update transaction status to cancelled
                    if (in_array($transaction->status, ['pending', 'processing'])) {
                        $transaction->update([
                            'status' => 'cancelled',
                            'notes' => 'Payment cancelled by user'
                        ]);
                    }

                    return view('payment.cancelled', [
                        'transaction' => $transaction,
                        'message' => 'Payment was cancelled. No charges have been made to your account.'
                    ]);
                }
            }

            // Fallback for cancel without transaction ID
            return view('payment.cancelled', [
                'message' => 'Payment was cancelled. You can try again anytime.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in payment cancel handler: ' . $e->getMessage());

            return view('payment.cancelled', [
                'message' => 'Payment was cancelled.'
            ]);
        }
    }

    /**
     * Display iPaymu transactions data for dashboard
     */
    public function ipaymuTransactions(Request $request)
    {
        $query = Transaction::whereNotNull('ipaymu_transaction_id')
                           ->with(['customer', 'items.product'])
                           ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('ipaymu_payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by transaction number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('ipaymu_transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'total_transactions' => Transaction::whereNotNull('ipaymu_transaction_id')->count(),
            'completed_transactions' => Transaction::whereNotNull('ipaymu_transaction_id')->where('status', 'completed')->count(),
            'pending_transactions' => Transaction::whereNotNull('ipaymu_transaction_id')->where('status', 'processing')->count(),
            'failed_transactions' => Transaction::whereNotNull('ipaymu_transaction_id')->where('status', 'failed')->count(),
            'total_amount' => Transaction::whereNotNull('ipaymu_transaction_id')->where('status', 'completed')->sum('total'),
            'total_fees' => Transaction::whereNotNull('ipaymu_transaction_id')->where('status', 'completed')->sum('ipaymu_fee'),
        ];

        // Get payment methods breakdown
        $paymentMethods = Transaction::whereNotNull('ipaymu_transaction_id')
                                   ->where('status', 'completed')
                                   ->selectRaw('ipaymu_payment_method, COUNT(*) as count, SUM(total) as total_amount')
                                   ->groupBy('ipaymu_payment_method')
                                   ->get();

        return view('dashboard.ipaymu-transactions', compact('transactions', 'stats', 'paymentMethods'));
    }

    /**
     * Show specific iPaymu transaction details
     */
    public function showIpaymuTransaction(Transaction $transaction)
    {
        if (!$transaction->ipaymu_transaction_id) {
            abort(404, 'Transaction not found or not an iPaymu transaction');
        }

        $transaction->load(['customer', 'items.product', 'user']);

        return view('dashboard.ipaymu-transaction-detail', compact('transaction'));
    }

    /**
     * Handle payment failed redirect
     */
    public function paymentFailed(Request $request)
    {
        try {
            $transactionId = $request->query('transaction_id');
            $errorReason = $request->query('error', 'Payment failed');

            Log::info('Payment failed redirect', [
                'transaction_id' => $transactionId,
                'error_reason' => $errorReason,
                'query_params' => $request->query()
            ]);

            if ($transactionId) {
                $transaction = Transaction::with('items.product')->find($transactionId);
                if ($transaction) {
                    // Update transaction status to failed
                    if (in_array($transaction->status, ['pending', 'processing'])) {
                        $transaction->update([
                            'status' => 'failed',
                            'notes' => 'Payment failed: ' . $errorReason
                        ]);
                    }

                    return view('payment.failed', [
                        'transaction' => $transaction,
                        'message' => 'Payment could not be processed. Please try again.',
                        'error_reason' => $errorReason
                    ]);
                }
            }

            // Fallback for failed without transaction ID
            return view('payment.failed', [
                'message' => 'Payment could not be processed. Please try again.',
                'error_reason' => $errorReason
            ]);

        } catch (\Exception $e) {
            Log::error('Error in payment failed handler: ' . $e->getMessage());

            return view('payment.failed', [
                'message' => 'Payment could not be processed.'
            ]);
        }
    }
}
