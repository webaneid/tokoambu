<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerLoginRequest;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CustomerLoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('storefront.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(CustomerLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // Attempt to authenticate
        if (!Auth::guard('customer')->attempt($credentials, $request->remember)) {
            return back()->withErrors([
                'email' => 'Kombinasi email dan password tidak sesuai.',
            ]);
        }

        // Regenerate session to prevent session fixation attacks
        $request->session()->regenerate();

        // Migrate any guest cart to customer's cart
        $this->migrateGuestCart();

        return redirect()->intended('/shop')->with('success', 'Berhasil masuk! Selamat datang kembali.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy()
    {
        Auth::guard('customer')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/account/login');
    }

    /**
     * Migrate guest cart to customer's cart after login.
     */
    private function migrateGuestCart()
    {
        $sessionId = Session::getId();
        $customer = Auth::guard('customer')->user();

        // Find any existing guest cart
        $guestCart = Cart::where('session_id', $sessionId)
            ->whereNull('customer_id')
            ->first();

        if ($guestCart) {
            // Get or create customer's cart
            $customerCart = Cart::firstOrCreate(
                ['customer_id' => $customer->id],
                ['customer_id' => $customer->id]
            );

            // Migrate items from guest cart to customer cart
            $guestCart->items()->update(['cart_id' => $customerCart->id]);

            // Delete old guest cart
            $guestCart->delete();
        }
    }
}
