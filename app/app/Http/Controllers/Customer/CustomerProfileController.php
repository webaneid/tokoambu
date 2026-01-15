<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomerProfileRequest;
use App\Models\Province;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
{
    /**
     * Display customer profile
     */
    public function show(): View
    {
        $customer = auth('customer')->user();
        $provinces = Province::orderBy('name')->get();

        // Get customer's bank account (first one if exists)
        $bankAccount = $customer->bankAccounts()->first();

        return view('storefront.customer.profile.show', [
            'customer' => $customer,
            'provinces' => $provinces,
            'bankAccount' => $bankAccount,
        ]);
    }

    /**
     * Update customer profile
     */
    public function update(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        $customer = auth('customer')->user();

        $customer->update($request->only([
            'name',
            'email',
            'phone',
            'whatsapp_number',
            'address',
            'province_id',
            'city_id',
            'district_id',
            'postal_code',
        ]));

        // Update or create bank account if any bank fields are filled
        if ($request->filled('bank_name') || $request->filled('account_number') || $request->filled('account_name')) {
            $customer->bankAccounts()->updateOrCreate(
                ['customer_id' => $customer->id],
                [
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_name' => $request->account_name,
                ]
            );
        }

        return redirect()->route('customer.profile')
            ->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Update customer password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password:customer'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.required' => 'Kata sandi saat ini diperlukan',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai',
            'password.required' => 'Kata sandi baru diperlukan',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai',
            'password.min' => 'Kata sandi minimal 8 karakter',
        ]);

        $customer = auth('customer')->user();
        $customer->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('customer.profile')
            ->with('success', 'Kata sandi berhasil diperbarui');
    }
}
