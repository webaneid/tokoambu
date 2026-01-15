<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store()
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return redirect('/login');
        }

        if ($customer->hasVerifiedEmail()) {
            return redirect('/shop')->with('info', 'Email sudah terverifikasi.');
        }

        $customer->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
