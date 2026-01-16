<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PreorderPeriod;
use App\Models\Product;
use App\Models\Setting;
use App\Notifications\PreorderProductReadyNotification;
use Illuminate\Http\Request;

class PreorderController extends Controller
{
    /**
     * Display preorder dashboard (list of all preorder products)
     * Tampilkan semua produk yang allow_preorder = true (termasuk yang belum punya order)
     */
    public function index()
    {
        $productOptions = Product::where('allow_preorder', true)
            ->where('is_active', true)
            ->select('id', 'name', 'sku')
            ->orderBy('name')
            ->get();

        $productsQuery = Product::where('allow_preorder', true)
            ->where('is_active', true)
            ->when(request('product_id'), function ($query) {
                $query->where('id', request('product_id'));
            })
            ->with(['orders' => function ($query) {
                $query->where('type', 'preorder')
                    ->whereIn('status', ['waiting_dp', 'dp_paid', 'product_ready', 'waiting_payment'])
                    ->with('customer');
            }, 'featuredMedia'])
            ->get();

        $products = $productsQuery;

        return view('preorders.index', compact('products', 'productOptions'));
    }

    /**
     * Display preorder details for a specific product
     */
    public function show(Product $product)
    {
        if (!$product->allow_preorder) {
            abort(404, 'Product is not available for preorder');
        }

        // Get all periods for this product
        $periods = $product->preorderPeriods()
            ->orderBy('start_date', 'desc')
            ->get();

        $orders = Order::where('type', 'preorder')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->whereIn('status', ['waiting_dp', 'dp_paid', 'product_ready', 'waiting_payment'])
            ->with(['customer', 'items', 'preorderPeriod'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group orders by status
        $ordersByStatus = [
            'waiting_dp' => $orders->where('status', 'waiting_dp'),
            'dp_paid' => $orders->where('status', 'dp_paid'),
            'product_ready' => $orders->where('status', 'product_ready'),
            'waiting_payment' => $orders->where('status', 'waiting_payment'),
        ];

        // Calculate statistics
        $stats = [
            'total_orders' => $orders->count(),
            'total_qty' => $orders->sum(function ($order) use ($product) {
                return $order->items->where('product_id', $product->id)->sum('quantity');
            }),
            'total_dp_collected' => $orders->where('status', '!=', 'waiting_dp')->sum('paid_amount'),
            'total_revenue_potential' => $orders->sum('total_amount'),
        ];

        return view('preorders.show', compact('product', 'orders', 'ordersByStatus', 'stats', 'periods'));
    }

    /**
     * Get WhatsApp message template for specific order and type
     */
    public function getWhatsAppMessage(Order $order, string $type)
    {
        $template = Setting::getPreorderWaTemplate($type);

        // Load items with product relationship
        $order->load('items.product', 'items.productVariant');

        // Build items list
        $itemsList = $order->items->map(function ($item) {
            $productName = $item->product->name ?? 'Unknown Product';
            if ($item->productVariant) {
                $productName .= ' - ' . $item->productVariant->sku;
            }
            return "- {$productName} ({$item->quantity} pcs) @ Rp " . number_format($item->unit_price, 0, ',', '.');
        })->join("\n");

        // Product names for backward compatibility
        $productNames = $order->items->map(function ($item) {
            $name = $item->product->name ?? 'Unknown';
            if ($item->productVariant) {
                $name .= ' - ' . $item->productVariant->sku;
            }
            return $name;
        })->join(', ');

        // Get store info from settings
        $storeName = Setting::get('shop_name', 'Toko Ambu');
        $storePhone = Setting::get('shop_whatsapp', '');
        $storeWebsite = Setting::get('shop_website', '');

        // Generate invoice URL (signed URL for public access, always uses storefront domain)
        $invoiceUrl = \App\Http\Controllers\InvoiceController::generatePublicUrl($order);

        $variables = [
            'customer_name' => $order->customer->name,
            'order_number' => $order->order_number,
            'product_name' => $productNames, // Backward compatibility
            'items' => $itemsList,
            'total_qty' => $order->items->sum('quantity'),
            'total_amount' => number_format($order->total_amount, 0, ',', '.'),
            'dp_amount' => number_format($order->dp_amount, 0, ',', '.'),
            'remaining_amount' => number_format($order->remainingFinalAmount(), 0, ',', '.'),
            'deadline' => match ($type) {
                'dp_reminder' => $order->dp_payment_deadline?->format('d/m/Y H:i'),
                'product_ready', 'final_reminder' => $order->final_payment_deadline?->format('d/m/Y H:i'),
                default => '',
            },
            'invoice_url' => $invoiceUrl,
            'store_name' => $storeName,
            'store_phone' => $storePhone,
            'store_website' => $storeWebsite,
        ];

        $message = Setting::parseWaTemplate($template, $variables);

        // Encode for WhatsApp URL
        $phone = $order->customer->phone;
        $whatsappUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone) . '?text=' . urlencode($message);

        return response()->json([
            'message' => $message,
            'url' => $whatsappUrl,
        ]);
    }

    /**
     * Mark product as ready (stock available, notify customers)
     */
    public function markProductReady(Product $product)
    {
        // Update all dp_paid orders for this product to product_ready
        $orders = Order::where('type', 'preorder')
            ->where('status', 'dp_paid')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->get();

        foreach ($orders as $order) {
            $deadline = now()->addDays(Setting::getPreorderFinalDeadlineDays());
            $order->update([
                'status' => 'product_ready',
                'final_payment_deadline' => $deadline,
            ]);

            $order->customer?->notify(new PreorderProductReadyNotification(
                $order->id,
                $order->order_number,
                $deadline->format('d/m/Y H:i'),
                route('customer.payment.select', $order->id)
            ));
        }

        return redirect()->back()->with('success', "Produk {$product->name} ditandai siap. {$orders->count()} pelanggan akan dinotifikasi untuk melakukan pelunasan.");
    }

    /**
     * Create new preorder period for a product
     */
    public function createPeriod(Product $product)
    {
        if (!$product->allow_preorder) {
            abort(404, 'Product is not available for preorder');
        }

        return view('preorders.periods.create', compact('product'));
    }

    /**
     * Store new preorder period
     */
    public function storePeriod(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'target_quantity' => 'nullable|integer|min:1',
        ]);

        $period = $product->preorderPeriods()->create($validated);

        return redirect()
            ->route('preorders.show', $product)
            ->with('success', "Periode preorder '{$period->name}' berhasil dibuat.");
    }

    /**
     * Close a preorder period
     */
    public function closePeriod(PreorderPeriod $period)
    {
        $period->close();

        return redirect()
            ->back()
            ->with('success', "Periode '{$period->name}' ditutup. Tidak ada order baru yang bisa masuk ke periode ini.");
    }

    /**
     * Archive a preorder period
     */
    public function archivePeriod(PreorderPeriod $period)
    {
        $period->archive();

        return redirect()
            ->back()
            ->with('success', "Periode '{$period->name}' berhasil diarsipkan.");
    }

    /**
     * Reopen a preorder period
     */
    public function reopenPeriod(PreorderPeriod $period)
    {
        $period->reopen();

        return redirect()
            ->back()
            ->with('success', "Periode '{$period->name}' dibuka kembali.");
    }
}
