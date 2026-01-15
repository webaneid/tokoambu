<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['name', 'email', 'phone', 'is_active'];
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $vendors = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'bank_accounts' => 'sometimes|array',
            'bank_accounts.*.bank_name' => 'required_with:bank_accounts|string|max:255',
            'bank_accounts.*.bank_code' => 'nullable|string|max:20',
            'bank_accounts.*.account_number' => 'required_with:bank_accounts|string|max:50',
            'bank_accounts.*.account_name' => 'required_with:bank_accounts|string|max:255',
        ]);

        $bankAccounts = $validated['bank_accounts'] ?? [];
        unset($validated['bank_accounts']);

        $vendor = Vendor::create($validated);

        foreach ($bankAccounts as $account) {
            if (!empty($account['bank_name']) && !empty($account['account_number']) && !empty($account['account_name'])) {
                $vendor->bankAccounts()->create($account);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Vendor berhasil ditambahkan',
                'vendor' => $vendor,
            ], 201);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil ditambahkan');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load('bankAccounts');
        return view('vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('bankAccounts');
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'bank_accounts' => 'sometimes|array',
            'bank_accounts.*.id' => 'nullable|exists:bank_accounts,id',
            'bank_accounts.*.bank_name' => 'required_with:bank_accounts|string|max:255',
            'bank_accounts.*.bank_code' => 'nullable|string|max:20',
            'bank_accounts.*.account_number' => 'required_with:bank_accounts|string|max:50',
            'bank_accounts.*.account_name' => 'required_with:bank_accounts|string|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $bankAccounts = $validated['bank_accounts'] ?? [];
        unset($validated['bank_accounts']);

        $vendor->update($validated);

        // Get existing bank account IDs
        $existingIds = $vendor->bankAccounts->pluck('id')->toArray();
        $submittedIds = [];

        // Update or create bank accounts
        foreach ($bankAccounts as $account) {
            if (!empty($account['bank_name']) && !empty($account['account_number']) && !empty($account['account_name'])) {
                if (!empty($account['id'])) {
                    // Update existing
                    $bankAccount = $vendor->bankAccounts()->find($account['id']);
                    if ($bankAccount) {
                        $bankAccount->update($account);
                        $submittedIds[] = $account['id'];
                    }
                } else {
                    // Create new
                    $vendor->bankAccounts()->create($account);
                }
            }
        }

        // Delete bank accounts that were removed
        $toDelete = array_diff($existingIds, $submittedIds);
        if (!empty($toDelete)) {
            $vendor->bankAccounts()->whereIn('id', $toDelete)->delete();
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil diperbarui');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->bankAccounts()->delete();
        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil dihapus');
    }
}
