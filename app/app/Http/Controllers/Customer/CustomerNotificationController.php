<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerNotificationController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user();

        $notifications = $customer->notifications()
            ->latest()
            ->paginate(15);

        return view('storefront.customer.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $notification)
    {
        $customer = auth('customer')->user();

        $item = $customer->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $item->markAsRead();

        return redirect()->back();
    }
}
