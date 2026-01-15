<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->withCount('orders');
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['name', 'email', 'phone', 'orders_count', 'is_active'];
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $customers = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'whatsapp_number' => 'nullable|string|regex:/^\\+?[0-9]{6,20}$/',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'postal_code' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
            'bank_accounts' => 'nullable|array',
            'bank_accounts.*.bank_name' => 'required|string|max:255',
            'bank_accounts.*.bank_code' => 'nullable|string|max:20',
            'bank_accounts.*.account_number' => 'required|string|max:50',
            'bank_accounts.*.account_name' => 'required|string|max:255',
        ]);

        $customer = Customer::create($validated);

        // Create bank accounts if provided
        if (isset($validated['bank_accounts']) && is_array($validated['bank_accounts'])) {
            foreach ($validated['bank_accounts'] as $bankAccount) {
                $customer->bankAccounts()->create($bankAccount);
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer berhasil ditambahkan');
    }

    public function show(Customer $customer)
    {
        $customer->load(['province', 'city', 'district']);
        $orders = $customer->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);
        return view('customers.show', compact('customer', 'orders'));
    }

    public function edit(Customer $customer)
    {
        $customer->load('bankAccounts');
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'whatsapp_number' => 'nullable|string|regex:/^\\+?[0-9]{6,20}$/',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'postal_code' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil diperbarui');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus');
    }

    public function storeBankAccount(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'nullable|string|max:20',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
        ]);

        $account = $customer->bankAccounts()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Rekening customer berhasil ditambahkan',
                'account' => $account,
            ], 201);
        }

        return redirect()->route('customers.show', $customer)->with('success', 'Rekening customer berhasil ditambahkan');
    }

    public function deleteBankAccount(Customer $customer, BankAccount $account)
    {
        if ($account->customer_id !== $customer->id) {
            abort(403);
        }
        $account->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Rekening customer berhasil dihapus']);
        }

        return redirect()->route('customers.show', $customer)->with('success', 'Rekening customer berhasil dihapus');
    }
}
