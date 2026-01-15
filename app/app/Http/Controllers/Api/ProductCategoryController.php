<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    // Get all categories with search
    public function indexCategories(Request $request)
    {
        $search = $request->query('search', '');
        $categories = ProductCategory::where('name', 'like', "%{$search}%")
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    // Store new category
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_categories',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = $this->buildSlug($validated['name']);

        $category = ProductCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $category,
        ], 201);
    }

    // Get all suppliers with search
    public function indexSuppliers(Request $request)
    {
        $search = $request->query('search', '');
        $suppliers = Supplier::where('name', 'like', "%{$search}%")
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($suppliers);
    }

    // Store new supplier
    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers',
            'email' => 'nullable|email|unique:suppliers',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = true;
        $supplier = Supplier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => $supplier,
        ], 201);
    }

    // Get custom fields for a category
    public function getCustomFields($id)
    {
        $category = ProductCategory::findOrFail($id);

        return response()->json([
            'custom_fields' => $category->custom_fields ?? [],
        ]);
    }

    private function buildSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'kategori';

        $suffix = 1;
        while (ProductCategory::where('slug', $slug)->exists()) {
            $slug = $baseSlug !== '' ? "{$baseSlug}-{$suffix}" : "kategori-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
