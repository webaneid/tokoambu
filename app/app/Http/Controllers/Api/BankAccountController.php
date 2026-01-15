<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'bank_name' => 'required|string|max:100',
            'bank_code' => 'nullable|string|max:10',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:100',
        ]);

        $bankAccount = BankAccount::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Rekening customer berhasil ditambahkan',
            'bank_account' => [
                'id' => $bankAccount->id,
                'customer_id' => $bankAccount->customer_id,
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'account_name' => $bankAccount->account_name,
            ],
        ], 201);
    }
}
