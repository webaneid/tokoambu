<?php

namespace App\Http\Controllers;

use App\Models\FinancialCategory;
use Illuminate\Http\Request;

class FinancialCategoryController extends Controller
{
    protected function isLocked(FinancialCategory $category): bool
    {
        return $category->is_default === true;
    }

    public function index(Request $request)
    {
        $query = FinancialCategory::query();
        $search = trim((string) $request->query('q', ''));
        $type = $request->query('type');

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }
        if (in_array($type, ['income', 'expense'], true)) {
            $query->where('type', $type);
        }

        $allowedSorts = ['name', 'type', 'is_active'];
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $categories = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();

        return view('financial-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('financial-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:income,expense',
        ]);

        $category = FinancialCategory::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Kategori ditambahkan',
                'category' => $category,
            ], 201);
        }

        return redirect()->route('financial-categories.index')->with('success', 'Kategori keuangan ditambahkan');
    }

    public function edit(FinancialCategory $financialCategory)
    {
        return view('financial-categories.edit', compact('financialCategory'));
    }

    public function update(Request $request, FinancialCategory $financialCategory)
    {
        if ($this->isLocked($financialCategory)) {
            return back()->with('error', 'Kategori default tidak boleh diubah.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:income,expense',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $financialCategory->update($validated);

        return redirect()->route('financial-categories.index')->with('success', 'Kategori keuangan berhasil diperbarui.');
    }

    public function destroy(FinancialCategory $financialCategory)
    {
        if ($this->isLocked($financialCategory)) {
            return back()->with('error', 'Kategori default tidak boleh dihapus.');
        }

        if ($financialCategory->ledgerEntries()->exists()) {
            return back()->with('error', 'Kategori sudah dipakai di transaksi.');
        }

        $financialCategory->delete();

        return redirect()->route('financial-categories.index')->with('success', 'Kategori keuangan berhasil dihapus.');
    }
}
