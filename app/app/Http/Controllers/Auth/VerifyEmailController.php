<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated customer's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return redirect('/login');
        }

        if ($customer->hasVerifiedEmail()) {
            return redirect('/shop')->with('info', 'Email sudah terverifikasi sebelumnya.');
        }

        if ($customer->markEmailAsVerified()) {
            event(new Verified($customer));
        }

        return redirect('/shop')->with('success', 'Email berhasil diverifikasi!');
    }
}
