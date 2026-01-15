<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\ImageService;
use App\Services\IPaymuService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

class CustomerPaymentController extends Controller
{
    /**
     * Show payment method selection page
     */
    public function selectMethod(Order $order): View
    {
        $this->authorizePaymentAccess($order);

        // Get enabled payment methods from settings
        $paymentMethods = [
            'cod' => [
                'enabled' => (bool) Setting::get('payment_method_cod', false),
                'name' => 'COD (Cash on Delivery)',
                'description' => 'Bayar saat barang diterima',
                'icon' => 'cash',
            ],
            'bank_transfer' => [
                'enabled' => (bool) Setting::get('payment_method_bank_transfer', false),
                'name' => 'Transfer Bank',
                'description' => 'Transfer ke rekening toko',
                'icon' => 'bank',
            ],
            'ewallet' => [
                'enabled' => (bool) Setting::get('payment_method_ewallet', false),
                'name' => 'E-Wallet',
                'description' => 'QRIS, GoPay, OVO, Dana, dll',
                'icon' => 'wallet',
            ],
            'ipaymu' => [
                'enabled' => (bool) Setting::get('payment_method_ipaymu', false),
                'name' => 'iPaymu',
                'description' => 'Virtual Account & berbagai metode',
                'icon' => 'credit-card',
            ],
        ];

        // Filter only enabled methods
        $enabledMethods = collect($paymentMethods)->filter(function ($method) {
            return $method['enabled'];
        });

        // Calculate payment amount
        $dpRequired = Setting::isPreorderDpRequired();
        $isDpPayment = $order->isPreorder() && $dpRequired && in_array($order->status, ['waiting_dp', 'draft']);

        $paymentAmount = $order->total_amount;
        if ($isDpPayment) {
            $dpPercentage = Setting::getPreorderDpPercentage();
            $paymentAmount = ($order->total_amount * $dpPercentage) / 100;
        }

        return view('storefront.customer.payment.select-method', [
            'order' => $order,
            'paymentMethods' => $enabledMethods,
            'paymentAmount' => $paymentAmount,
            'isDpPayment' => $isDpPayment,
            'paymentRoutes' => $this->paymentRoutes($order),
        ]);
    }

    /**
     * Show bank transfer details page
     */
    public function bankTransfer(Order $order): View
    {
        $this->authorizePaymentAccess($order);

        // Check if bank transfer is enabled
        if (!(bool) Setting::get('payment_method_bank_transfer', false)) {
            abort(404, 'Payment method not available');
        }

        // Get company bank accounts
        $bankAccounts = BankAccount::whereNotNull('user_id')
            ->orderBy('bank_name')
            ->get();

        // Calculate payment amount
        $dpRequired = Setting::isPreorderDpRequired();
        $isDpPayment = $order->isPreorder() && $dpRequired && in_array($order->status, ['waiting_dp', 'draft']);

        $paymentAmount = $order->total_amount - $order->paid_amount;
        $minimumDp = null;

        if ($isDpPayment) {
            $dpPercentage = Setting::getPreorderDpPercentage();
            $minimumDp = ($order->total_amount * $dpPercentage) / 100;
        }

        return view('storefront.customer.payment.bank-transfer', [
            'order' => $order,
            'bankAccounts' => $bankAccounts,
            'paymentAmount' => $paymentAmount,
            'isDpPayment' => $isDpPayment,
            'minimumDp' => $minimumDp,
            'paymentRoutes' => $this->paymentRoutes($order),
        ]);
    }

    /**
     * Show bank transfer confirmation form
     */
    public function confirmBankTransfer(Order $order)
    {
        $this->authorizePaymentAccess($order);

        // Check if bank transfer is enabled
        if (!(bool) Setting::get('payment_method_bank_transfer', false)) {
            abort(404, 'Payment method not available');
        }

        // Get company bank accounts
        $bankAccounts = BankAccount::whereNotNull('user_id')
            ->orderBy('bank_name')
            ->get();

        // Calculate payment amount
        $dpRequired = Setting::isPreorderDpRequired();
        $isDpPayment = $order->isPreorder() && $dpRequired && in_array($order->status, ['waiting_dp', 'draft']);

        $paymentAmount = $order->total_amount - $order->paid_amount;
        $minimumDp = null;

        if ($isDpPayment) {
            $dpPercentage = Setting::getPreorderDpPercentage();
            $minimumDp = ($order->total_amount * $dpPercentage) / 100;
        }

        return view('storefront.customer.payment.bank-transfer-confirm', [
            'order' => $order,
            'bankAccounts' => $bankAccounts,
            'paymentAmount' => $paymentAmount,
            'isDpPayment' => $isDpPayment,
            'minimumDp' => $minimumDp,
            'paymentRoutes' => $this->paymentRoutes($order),
        ]);
    }

    /**
     * Store bank transfer confirmation
     */
    public function storeBankTransfer(Order $order)
    {
        $this->authorizePaymentAccess($order);

        $validated = request()->validate([
            'amount' => 'required|numeric|min:0.01',
            'sender_name' => 'required|string|max:255',
            'sender_bank' => 'required|string|max:255',
            'shop_bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_proof' => 'required|image|max:10240',
            'notes' => 'nullable|string',
        ]);

        // Validate minimum DP for preorder
        if ($order->type === 'preorder' && $order->status === 'waiting_dp') {
            $dpPercentage = Setting::getPreorderDpPercentage();
            $minimumDp = ($order->total_amount * $dpPercentage) / 100;

            if ($validated['amount'] < $minimumDp) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'amount' => "Minimal transfer DP {$dpPercentage}% yaitu Rp " . number_format($minimumDp, 0, ',', '.')
                    ]);
            }
        }

        $payment = \App\Models\Payment::create([
            'order_id' => $order->id,
            'amount' => $validated['amount'],
            'method' => 'transfer',
            'sender_name' => $validated['sender_name'],
            'sender_bank' => $validated['sender_bank'],
            'shop_bank_account_id' => $validated['shop_bank_account_id'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'paid_at' => now(),
        ]);

        $file = $validated['payment_proof'];
        $folder = 'media/payment_proof';
        $imageService = app(ImageService::class);
        $processed = $imageService->processProofImage($file, $folder);
        $uploaderId = User::role('Super Admin')->value('id') ?? User::value('id');

        \App\Models\Media::create([
            'type' => 'payment_proof',
            'filename' => $file->getClientOriginalName(),
            'path' => $processed['path'],
            'mime' => $processed['mime'],
            'size' => $processed['size'],
            'metadata' => [
                'extension' => $processed['extension'],
                'original_mime' => $file->getClientMimeType(),
                'order_id' => $payment->order_id,
                'customer_id' => $order->customer_id,
            ],
            'uploaded_by' => $uploaderId,
            'payment_id' => $payment->id,
        ]);

        if (auth('customer')->check()) {
            return redirect()->route('customer.order.show', $order->id)
                ->with('success', 'Konfirmasi pembayaran berhasil dikirim. Admin akan memverifikasi pembayaran Anda.');
        }

        return redirect($this->publicInvoiceUrl($order))
            ->with('success', 'Konfirmasi pembayaran berhasil dikirim. Admin akan memverifikasi pembayaran Anda.');
    }

    /**
     * Show iPaymu payment page
     */
    public function ipaymu(Order $order): View|RedirectResponse
    {
        $this->authorizePaymentAccess($order);

        // Check if iPaymu is enabled
        if (!(bool) Setting::get('payment_method_ipaymu', false)) {
            abort(404, 'Payment method not available');
        }

        // Calculate payment amount
        $dpRequired = Setting::isPreorderDpRequired();
        $isDpPayment = $order->isPreorder() && $dpRequired && in_array($order->status, ['waiting_dp', 'draft']);

        $paymentAmount = $order->total_amount;
        if ($isDpPayment) {
            $dpPercentage = Setting::getPreorderDpPercentage();
            $paymentAmount = ($order->total_amount * $dpPercentage) / 100;
        }

        // Get available payment channels
        try {
            $ipaymuService = new IPaymuService();
            $channelsData = $ipaymuService->getPaymentChannels();
            $channels = $channelsData['Success'] ?? false ? $channelsData['Data'] : [];
        } catch (\Exception $e) {
            $channels = [];
        }

        return view('storefront.customer.payment.ipaymu', [
            'order' => $order,
            'paymentAmount' => $paymentAmount,
            'isDpPayment' => $isDpPayment,
            'channels' => $channels,
            'paymentRoutes' => $this->paymentRoutes($order),
        ]);
    }

    /**
     * Create iPaymu payment transaction
     */
    public function createIpaymuPayment(Order $order): RedirectResponse
    {
        $this->authorizePaymentAccess($order);

        // Check if iPaymu is enabled
        if (!(bool) Setting::get('payment_method_ipaymu', false)) {
            return redirect()->back()->with('error', 'Metode pembayaran tidak tersedia');
        }

        try {
            $ipaymuService = new IPaymuService();

            // Calculate payment amount
            $dpRequired = Setting::isPreorderDpRequired();
            $isDpPayment = $order->isPreorder() && $dpRequired && in_array($order->status, ['waiting_dp', 'draft']);

            $paymentAmount = $order->total_amount;
            if ($isDpPayment) {
                $dpPercentage = Setting::getPreorderDpPercentage();
                $paymentAmount = ($order->total_amount * $dpPercentage) / 100;
            }

            // Get selected payment channel
            $paymentChannelValue = request('payment_channel');
            if (!$paymentChannelValue) {
                return redirect()->back()->with('error', 'Pilih metode pembayaran terlebih dahulu');
            }

            [$paymentMethod, $paymentChannel] = explode(':', $paymentChannelValue);

            // Create payment
            $customer = $order->customer;
            $result = $ipaymuService->createPayment(
                $order->id,
                $paymentAmount,
                $customer->email,
                $customer->name,
                $customer->phone ?? '08123456789',
                $paymentMethod,
                $paymentChannel
            );

            if (isset($result['success']) && $result['success'] === false) {
                return redirect()->back()->with('error', $result['message'] ?? 'Gagal membuat pembayaran');
            }

            // Save payment data to order or session
            session([
                'ipaymu_transaction_' . $order->id => [
                    'reference_id' => $result['Data']['ReferenceId'] ?? null,
                    'payment_no' => $result['Data']['PaymentNo'] ?? null,
                    'payment_name' => $result['Data']['PaymentName'] ?? null,
                    'via' => $result['Data']['Via'] ?? null,
                    'channel' => $result['Data']['Channel'] ?? null,
                    'qr_image' => $result['Data']['QrImage'] ?? null,
                    'qr_template' => $result['Data']['QrTemplate'] ?? null,
                    'qr_string' => $result['Data']['QrString'] ?? null,
                    'payment_url' => $result['Data']['PaymentUrl'] ?? null,
                    'expired' => $result['Data']['Expired'] ?? null,
                    'amount' => $paymentAmount,
                    'fee' => $result['Data']['Fee'] ?? 0,
                    'total' => $result['Data']['Total'] ?? $paymentAmount,
                ]
            ]);

            return redirect($this->paymentRoutes($order)['ipaymuResult']);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show iPaymu payment result page
     */
    public function ipaymuResult(Order $order): View|RedirectResponse
    {
        $this->authorizePaymentAccess($order);

        $transactionData = session('ipaymu_transaction_' . $order->id);

        if (!$transactionData) {
            return redirect($this->paymentRoutes($order)['ipaymu'])
                ->with('error', 'Data pembayaran tidak ditemukan');
        }

        return view('storefront.customer.payment.ipaymu-result', [
            'order' => $order,
            'transaction' => $transactionData,
        ]);
    }

    private function authorizePaymentAccess(Order $order): void
    {
        if (auth('customer')->check()) {
            if ($order->customer_id !== auth('customer')->id()) {
                abort(403, 'Unauthorized');
            }
            return;
        }

        if (!request()->hasValidSignature()) {
            abort(403, 'Unauthorized');
        }
    }

    private function paymentRoutes(Order $order): array
    {
        $expiresAt = now()->addDays(7);

        return [
            'select' => URL::temporarySignedRoute('customer.payment.select', $expiresAt, ['order' => $order->id]),
            'bankTransfer' => URL::temporarySignedRoute('customer.payment.bank-transfer', $expiresAt, ['order' => $order->id]),
            'bankTransferConfirm' => URL::temporarySignedRoute('customer.payment.bank-transfer.confirm', $expiresAt, ['order' => $order->id]),
            'bankTransferStore' => URL::temporarySignedRoute('customer.payment.bank-transfer.store', $expiresAt, ['order' => $order->id]),
            'ipaymu' => URL::temporarySignedRoute('customer.payment.ipaymu', $expiresAt, ['order' => $order->id]),
            'ipaymuCreate' => URL::temporarySignedRoute('customer.payment.ipaymu.create', $expiresAt, ['order' => $order->id]),
            'ipaymuResult' => URL::temporarySignedRoute('customer.payment.ipaymu-result', $expiresAt, ['order' => $order->id]),
        ];
    }

    private function publicInvoiceUrl(Order $order): string
    {
        return URL::signedRoute('invoices.public', ['order' => $order->id]);
    }
}
