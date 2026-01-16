<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Mail\InvoiceMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class InvoiceController extends Controller
{
    /**
     * Generate public invoice URL (always uses storefront domain)
     */
    public static function generatePublicUrl(Order $order): string
    {
        // Save current root URL
        $originalUrl = config('app.url');

        // Get storefront URL from config or construct from current URL
        $storefrontDomain = config('app.storefront_url');

        // If not configured, derive from current URL by removing admin. subdomain
        if (!$storefrontDomain) {
            $currentUrl = parse_url($originalUrl);
            $host = $currentUrl['host'] ?? 'localhost';
            $scheme = $currentUrl['scheme'] ?? 'http';
            $port = isset($currentUrl['port']) ? ':' . $currentUrl['port'] : '';

            // Remove admin. subdomain if present
            if (str_starts_with($host, 'admin.')) {
                $host = substr($host, 6); // Remove 'admin.'
            }

            $storefrontDomain = $scheme . '://' . $host . $port;
        }

        // Temporarily override root URL to storefront domain
        URL::forceRootUrl($storefrontDomain);

        $url = URL::signedRoute('invoices.public', ['order' => $order->id]);

        // Restore original URL config
        URL::forceRootUrl($originalUrl);

        return $url;
    }

    protected function storeInfo(): array
    {
        return [
            'name' => Setting::get('store_name', config('app.name', 'Toko Ambu')),
            'phone' => Setting::get('store_phone'),
            'address' => Setting::get('store_address'),
            'email' => Setting::get('store_email'),
            'city' => Setting::get('store_city'),
        ];
    }

    public function index()
    {
        $orders = Order::query()
            ->with(['customer', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('customer', 'items.product', 'payments', 'shipment');
        $store = $this->storeInfo();
        $publicUrl = self::generatePublicUrl($order);
        $publicDownloadUrl = URL::signedRoute('invoices.public_download', ['order' => $order->id]);
        return view('invoices.show', compact('order', 'store', 'publicUrl', 'publicDownloadUrl'));
    }

    public function download(Order $order)
    {
        $order->load('customer', 'items.product', 'payments', 'shipment');
        $store = $this->storeInfo();

        $pdf = Pdf::loadView('invoices.pdf', compact('order', 'store'))
            ->setPaper('a4')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);
        
        return $pdf->download('Invoice-' . $order->order_number . '.pdf');
    }

    public function print(Order $order)
    {
        $order->load('customer', 'items.product', 'payments', 'shipment');
        $store = $this->storeInfo();
        return view('invoices.print', compact('order', 'store'));
    }

    public function send(Order $order)
    {
        $order->load('customer', 'items.product', 'payments', 'shipment');
        $store = $this->storeInfo();

        if (empty($order->customer?->email)) {
            return back()->with('error', 'Email customer belum diisi.');
        }

        $pdf = Pdf::loadView('invoices.pdf', compact('order', 'store'))
            ->setPaper('a4')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);

        Mail::to($order->customer->email)->send(new InvoiceMail($order, $pdf->output()));
        $order->update(['invoice_sent_at' => now()]);

        return back()->with('success', 'Invoice berhasil dikirim ke email customer.');
    }

    public function publicShow(Order $order)
    {
        $order->load('customer', 'items.product', 'payments', 'shipment');
        $store = $this->storeInfo();
        $downloadUrl = URL::signedRoute('invoices.public_download', ['order' => $order->id]);
        $paymentUrl = URL::temporarySignedRoute('customer.payment.select', now()->addDays(7), ['order' => $order->id]);

        return view('invoices.public', compact('order', 'store', 'downloadUrl', 'paymentUrl'));
    }

    public function publicDownload(Order $order)
    {
        $order->load('customer', 'items.product', 'payments', 'shipment');
        $store = $this->storeInfo();

        $pdf = Pdf::loadView('invoices.pdf', compact('order', 'store'))
            ->setPaper('a4')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);

        return $pdf->download('Invoice-' . $order->order_number . '.pdf');
    }
}
