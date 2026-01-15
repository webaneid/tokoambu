<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderConfirmationController extends Controller
{
    /**
     * Show order confirmation page
     */
    public function show(Order $order): View
    {
        // Verify order belongs to authenticated customer
        if ($order->customer_id !== auth('customer')->id()) {
            abort(403, 'Unauthorized');
        }

        // Eager load relationships
        $order->load([
            'customer',
            'items.product',
            'items.productVariant',
            'shippingProvince',
            'shippingCity',
            'shippingDistrict',
        ]);

        $items = $order->items;
        $subtotal = $items->sum('subtotal');
        $shipping = $order->shipping_cost;
        $total = $order->total_amount;

        // Preorder settings
        $isPreorder = $order->type === 'preorder';
        $dpRequired = \App\Models\Setting::isPreorderDpRequired();
        $dpPercentage = \App\Models\Setting::getPreorderDpPercentage();
        $dpAmount = $isPreorder && $dpRequired ? round($total * ($dpPercentage / 100)) : 0;

        return view('storefront.order.confirmation', [
            'order' => $order,
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'isPreorder' => $isPreorder,
            'dpRequired' => $dpRequired,
            'dpPercentage' => $dpPercentage,
            'dpAmount' => $dpAmount,
        ]);
    }
}
