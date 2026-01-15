<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['name', 'position', 'email', 'phone', 'is_active'];
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $employees = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
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

        $employee = Employee::create($validated);

        foreach ($bankAccounts as $account) {
            if (!empty($account['bank_name']) && !empty($account['account_number']) && !empty($account['account_name'])) {
                $employee->bankAccounts()->create($account);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Karyawan berhasil ditambahkan',
                'employee' => $employee,
            ], 201);
        }

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function show(Employee $employee)
    {
        $employee->load('bankAccounts');
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('bankAccounts');
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
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

        $employee->update($validated);

        // Get existing bank account IDs
        $existingIds = $employee->bankAccounts->pluck('id')->toArray();
        $submittedIds = [];

        // Update or create bank accounts
        foreach ($bankAccounts as $account) {
            if (!empty($account['bank_name']) && !empty($account['account_number']) && !empty($account['account_name'])) {
                if (!empty($account['id'])) {
                    // Update existing
                    $bankAccount = $employee->bankAccounts()->find($account['id']);
                    if ($bankAccount) {
                        $bankAccount->update($account);
                        $submittedIds[] = $account['id'];
                    }
                } else {
                    // Create new
                    $employee->bankAccounts()->create($account);
                }
            }
        }

        // Delete bank accounts that were removed
        $toDelete = array_diff($existingIds, $submittedIds);
        if (!empty($toDelete)) {
            $employee->bankAccounts()->whereIn('id', $toDelete)->delete();
        }

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil diperbarui');
    }

    public function destroy(Employee $employee)
    {
        $employee->bankAccounts()->delete();
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil dihapus');
    }
}
