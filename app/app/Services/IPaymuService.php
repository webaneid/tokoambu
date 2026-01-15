<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Exception;

class IPaymuService
{
    private string $va;
    private string $apiKey;
    private string $mode;
    private string $baseUrl;

    public function __construct()
    {
        $this->va = Setting::get('ipaymu_va');
        $this->apiKey = Setting::get('ipaymu_api_key');
        $this->mode = Setting::get('ipaymu_mode', 'sandbox');

        if (!$this->va || !$this->apiKey) {
            throw new Exception('iPaymu credentials tidak dikonfigurasi di settings');
        }

        $this->baseUrl = $this->mode === 'production' 
            ? 'https://app.ipaymu.com/api/v2'
            : 'https://sandbox.ipaymu.com/api/v2';
    }

    /**
     * Get iPaymu mode (sandbox or production)
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Get available payment channels
     */
    public function getPaymentChannels()
    {
        try {
            $method = 'GET';
            $url = $this->baseUrl . '/payment-channels';

            $bodyHash = strtolower(hash('sha256', '{}'));
            $stringToSign = strtoupper($method) . ':' . $this->va . ':' . $bodyHash . ':' . $this->apiKey;
            $signature = hash_hmac('sha256', $stringToSign, $this->apiKey);
            $timestamp = date('YmdHis');

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'va' => $this->va,
                'signature' => $signature,
                'timestamp' => $timestamp,
            ])->get($url);

            return $response->json();
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil payment channels: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check account balance
     */
    public function checkBalance()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/account', [
                'account' => $this->va,
            ]);

            return $response->json();
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengecek saldo: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory($limit = 10)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/transaction/history', [
                'account' => $this->va,
                'limit' => $limit,
            ]);

            return $response->json();
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil histori transaksi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create Direct Payment (VA, QRIS, etc)
     */
    public function createPayment($orderId, $amount, $customerEmail, $customerName, $customerPhone = '08123456789', $paymentMethod = 'va', $paymentChannel = 'bni')
    {
        try {
            $body = [
                'account' => $this->va,
                'name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
                'amount' => (int) $amount,
                'notifyUrl' => route('ipaymu.notify'),
                'expired' => 24, // 24 jam
                'referenceId' => 'ORD-' . $orderId . '-' . time(),
                'paymentMethod' => $paymentMethod,
                'paymentChannel' => $paymentChannel,
                'product' => ['Order #' . $orderId],
                'qty' => [1],
                'price' => [(int) $amount],
            ];

            $bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);
            $method = 'POST';
            $url = $this->baseUrl . '/payment/direct';

            // Generate signature: METHOD:VA:requestBody:apiKey
            $bodyHash = strtolower(hash('sha256', $bodyJson));
            $stringToSign = strtoupper($method) . ':' . $this->va . ':' . $bodyHash . ':' . $this->apiKey;
            $signature = hash_hmac('sha256', $stringToSign, $this->apiKey);
            $timestamp = date('YmdHis');

            $response = Http::withBody($bodyJson, 'application/json')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'va' => $this->va,
                    'signature' => $signature,
                    'timestamp' => $timestamp,
                ])->post($url);

            return $response->json();
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus($referenceId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/transaction/check', [
                'account' => $this->va,
                'referenceId' => $referenceId,
            ]);

            return $response->json();
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengecek status transaksi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate webhook signature from iPaymu
     */
    public function validateSignature($data, $signature)
    {
        $computedSignature = hash_hmac('sha256', json_encode($data), $this->apiKey, true);
        $encodedSignature = base64_encode($computedSignature);

        return hash_equals($encodedSignature, $signature);
    }
}
