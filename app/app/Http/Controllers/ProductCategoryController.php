<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductCategory::query();
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['name', 'description'];
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $categories = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();

        return view('product-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('product-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:product_categories,name',
            'description' => 'nullable|string|max:255',
            'custom_fields' => 'nullable|json',
        ]);

        // Parse and validate custom fields structure
        if (!empty($validated['custom_fields'])) {
            $customFields = json_decode($validated['custom_fields'], true);

            // Validate each field has required properties
            foreach ($customFields as $field) {
                if (empty($field['label']) || empty($field['type'])) {
                    return back()->withInput()->with('error', 'Setiap custom field harus memiliki label dan tipe.');
                }
            }

            $validated['custom_fields'] = $customFields;
        } else {
            $validated['custom_fields'] = null;
        }

        $validated['slug'] = $this->buildSlug($validated['name']);

        ProductCategory::create($validated);

        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product-categories.edit', compact('productCategory'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:product_categories,name,' . $productCategory->id,
            'description' => 'nullable|string|max:255',
            'custom_fields' => 'nullable|json',
        ]);

        // Parse and validate custom fields structure
        if (!empty($validated['custom_fields'])) {
            $customFields = json_decode($validated['custom_fields'], true);

            // Validate each field has required properties
            foreach ($customFields as $field) {
                if (empty($field['label']) || empty($field['type'])) {
                    return back()->withInput()->with('error', 'Setiap custom field harus memiliki label dan tipe.');
                }
            }

            $validated['custom_fields'] = $customFields;
        } else {
            $validated['custom_fields'] = null;
        }

        $validated['slug'] = $this->buildSlug($validated['name'], $productCategory->id);

        $productCategory->update($validated);

        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil diperbarui.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->products()->exists()) {
            return back()->with('error', 'Kategori masih dipakai oleh produk.');
        }

        $productCategory->delete();

        return redirect()->route('product-categories.index')->with('success', 'Kategori produk berhasil dihapus.');
    }

    private function buildSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'kategori';

        $suffix = 1;
        while (ProductCategory::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug !== '' ? "{$baseSlug}-{$suffix}" : "kategori-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
