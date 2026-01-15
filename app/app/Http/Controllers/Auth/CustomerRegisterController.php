<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRegisterRequest;
use App\Models\Customer;
use App\Models\Cart;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerRegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        return view('storefront.auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(CustomerRegisterRequest $request)
    {
        // Create new customer
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Create empty cart for new customer
        Cart::create([
            'customer_id' => $customer->id,
        ]);

        // Fire registered event for email verification if needed
        event(new Registered($customer));

        // Login the customer
        Auth::guard('customer')->login($customer);

        return redirect('/shop')->with('success', 'Berhasil mendaftar! Selamat datang di Toko Ambu.');
    }
}
