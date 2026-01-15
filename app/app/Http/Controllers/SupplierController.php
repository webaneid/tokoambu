<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query()->withCount('products');
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['name', 'email', 'phone', 'products_count', 'is_active'];
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $suppliers = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'whatsapp_number' => 'nullable|string|regex:/^\\+?[0-9]{6,20}$/',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'bank_accounts' => 'sometimes|array',
            'bank_accounts.*.bank_name' => 'required_with:bank_accounts|string|max:255',
            'bank_accounts.*.bank_code' => 'nullable|string|max:20',
            'bank_accounts.*.account_number' => 'required_with:bank_accounts|string|max:50',
            'bank_accounts.*.account_name' => 'required_with:bank_accounts|string|max:255',
        ]);

        $bankAccounts = $validated['bank_accounts'] ?? [];
        unset($validated['bank_accounts']);

        $supplier = Supplier::create($validated);

        foreach ($bankAccounts as $account) {
            if (!empty($account['bank_name']) && !empty($account['account_number']) && !empty($account['account_name'])) {
                $supplier->bankAccounts()->create($account);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Supplier berhasil ditambahkan',
                'supplier' => $supplier,
            ], 201);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['province', 'city', 'district', 'products.category', 'bankAccounts']);
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'whatsapp_number' => 'nullable|string|regex:/^\\+?[0-9]{6,20}$/',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus');
    }

    public function storeBankAccount(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'nullable|string|max:20',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
        ]);

        $account = $supplier->bankAccounts()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Rekening supplier berhasil ditambahkan',
                'account' => $account,
            ], 201);
        }

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Rekening supplier berhasil ditambahkan');
    }

    public function deleteBankAccount(Supplier $supplier, BankAccount $account)
    {
        if ($account->supplier_id !== $supplier->id) {
            abort(403);
        }
        $account->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Rekening supplier berhasil dihapus']);
        }

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Rekening supplier berhasil dihapus');
    }
}
