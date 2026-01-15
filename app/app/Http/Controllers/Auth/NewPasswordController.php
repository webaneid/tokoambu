<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CustomerUser;
use App\Http\Requests\NewPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create()
    {
        return view('storefront.auth.reset-password', ['request' => request()]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(NewPasswordRequest $request)
    {
        // Here we will attempt to reset the customer's password. If it is successful we
        // will update the password on an actual customer model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (CustomerUser $customer) use ($request) {
                $customer->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($customer));
            }
        );

        // If the password was successfully reset, we will redirect the customer back to
        // the login page. If there is an error we can redirect them back with error.
        return $status == Password::PASSWORD_RESET
            ? redirect()->route('customer.login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
