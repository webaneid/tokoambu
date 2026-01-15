<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    /**
     * Display customer dashboard
     */
    public function dashboard(): View
    {
        $customer = auth('customer')->user();
        
        // Get statistics
        $totalOrders = Order::where('customer_id', $customer->id)->count();
        $totalSpent = Order::where('customer_id', $customer->id)
            ->sum('total_amount');
        $duePaymentOrders = Order::where('customer_id', $customer->id)
            ->whereIn('status', ['waiting_dp', 'waiting_payment', 'dp_paid', 'product_ready'])
            ->count();
        $inProcessOrders = Order::where('customer_id', $customer->id)
            ->whereIn('status', ['paid', 'packed', 'shipped'])
            ->count();
        
        // Get recent orders (last 5)
        $recentOrders = Order::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('storefront.customer.dashboard', [
            'customer' => $customer,
            'totalOrders' => $totalOrders,
            'totalSpent' => $totalSpent,
            'duePaymentOrders' => $duePaymentOrders,
            'inProcessOrders' => $inProcessOrders,
            'recentOrders' => $recentOrders,
        ]);
    }

    /**
     * Display order history
     */
    public function orders(Request $request): View
    {
        $customer = auth('customer')->user();
        
        $query = Order::where('customer_id', $customer->id)
            ->with(['items.product', 'items.productVariant']); // Eager load untuk performa

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->input('search') . '%');
        }

        // Sort by date (newest first)
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return view('storefront.customer.orders.index', [
            'orders' => $orders,
            'searchQuery' => $request->input('search'),
            'selectedStatus' => $request->input('status'),
        ]);
    }

    /**
     * Display single order detail
     */
    public function show(Order $order): View
    {
        // Verify order belongs to authenticated customer
        if ($order->customer_id !== auth('customer')->id()) {
            abort(403, 'Unauthorized');
        }

        // Load relationships
        $order->load([
            'items.product',
            'items.productVariant',
            'payments',
            'shippingProvince',
            'shippingCity',
            'shippingDistrict',
        ]);

        // Get store WhatsApp number
        $storeWhatsapp = \App\Models\Setting::get('store_whatsapp', '6281234567890');

        return view('storefront.customer.orders.show', [
            'order' => $order,
            'storeWhatsapp' => $storeWhatsapp,
        ]);
    }
}
