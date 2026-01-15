<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\IPaymuService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IPaymuWebhookController extends Controller
{
    /**
     * Proxy QRIS image to avoid CORS
     */
    public function proxyQr(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid URL');
        }

        if (!str_starts_with($url, 'https://sandbox.ipaymu.com') && !str_starts_with($url, 'https://my.ipaymu.com')) {
            abort(403, 'Forbidden');
        }

        try {
            $response = \Illuminate\Support\Facades\Http::get($url);

            return response($response->body(), 200)
                ->header('Content-Type', $response->header('Content-Type') ?? 'image/png')
                ->header('Cache-Control', 'public, max-age=3600');
        } catch (\Exception $e) {
            abort(500, 'Failed to fetch image');
        }
    }

    /**
     * Handle iPaymu payment notification webhook
     */
    public function notify(Request $request): JsonResponse
    {
        try {
            $ipaymuService = new IPaymuService();

            // Get signature from header
            $signature = $request->header('X-Ipaymu-Signature');
            $data = $request->all();

            // Validate signature
            if (!$ipaymuService->validateSignature($data, $signature)) {
                Log::warning('iPaymu webhook: Invalid signature', [
                    'data' => $data,
                    'signature' => $signature,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }

            // Extract order ID from reference ID
            $referenceId = $data['referenceId'] ?? null;
            if (!$referenceId || !preg_match('/ORD-(\d+)-/', $referenceId, $matches)) {
                Log::warning('iPaymu webhook: Invalid reference ID', ['data' => $data]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid reference ID',
                ], 400);
            }

            $orderId = $matches[1];
            $order = Order::find($orderId);

            if (!$order) {
                Log::warning('iPaymu webhook: Order not found', ['order_id' => $orderId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            // Check payment status
            $status = $data['status'] ?? null;
            $amount = $data['amount'] ?? 0;

            if ($status === 'success' || $status === 'paid') {
                DB::transaction(function () use ($order, $amount, $referenceId, $data) {
                    // Create payment record
                    $payment = Payment::create([
                        'order_id' => $order->id,
                        'amount' => $amount,
                        'method' => 'ipaymu',
                        'status' => 'verified',
                        'notes' => 'Pembayaran via iPaymu - ' . ($data['channel'] ?? 'Unknown'),
                        'paid_at' => now(),
                    ]);

                    // Update order paid amount
                    $totalPaid = $order->payments()->where('status', 'verified')->sum('amount');
                    $order->update(['paid_amount' => $totalPaid]);

                    // Update order status
                    if ($order->isPreorder()) {
                        // For preorder: check DP or full payment
                        if ($order->status === 'waiting_dp' && $totalPaid >= $order->dp_amount) {
                            $order->update([
                                'status' => 'dp_paid',
                                'dp_paid_at' => now(),
                            ]);
                        } elseif (in_array($order->status, ['product_ready', 'waiting_payment', 'dp_paid']) && $totalPaid >= $order->total_amount) {
                            $order->update(['status' => 'paid']);
                        }
                    } else {
                        // For regular order
                        if ($totalPaid >= $order->total_amount) {
                            $order->update(['status' => 'paid']);
                        } elseif ($totalPaid > 0) {
                            $order->update(['status' => 'dp_paid']);
                        }
                    }

                    // Create ledger entry
                    $category = \App\Models\FinancialCategory::firstOrCreate(
                        ['name' => 'Order', 'type' => 'income'],
                        ['is_active' => true]
                    );

                    \App\Models\LedgerEntry::create([
                        'entry_date' => now()->toDateString(),
                        'type' => 'income',
                        'category_id' => $category->id,
                        'description' => 'Pembayaran iPaymu order #' . $order->order_number,
                        'amount' => $amount,
                        'reference_id' => $order->id,
                        'reference_type' => 'order',
                        'source_type' => 'payment',
                        'source_id' => $payment->id,
                        'created_by' => null,
                    ]);
                });

                Log::info('iPaymu webhook: Payment successful', [
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'reference_id' => $referenceId,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed',
                ]);
            }

            // Handle failed or pending status
            Log::info('iPaymu webhook: Payment status not success', [
                'order_id' => $order->id,
                'status' => $status,
                'data' => $data,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification received',
            ]);

        } catch (\Exception $e) {
            Log::error('iPaymu webhook error: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }
}
