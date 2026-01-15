<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\VariantGenerator;
use App\Services\BulkPricingApplicator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    public function __construct(
        private VariantGenerator $variantGenerator,
        private BulkPricingApplicator $bulkPricing
    ) {}

    /**
     * Get all variants for a product.
     * For warehouse transfer, only return variants with stock.
     * For orders, return variants with stock OR if parent product allows preorder.
     */
    public function index(Product $product, Request $request)
    {
        $query = $product->variants()
            ->with('variantImage')
            ->orderBy('sku');

        // If this is for warehouse transfer (has_stock filter), only show variants with stock
        if ($request->has('has_stock')) {
            $query->whereHas('inventoryBalances', function ($q) {
                $q->where('qty_on_hand', '>', 0);
            });
        }

        // For orders, filter variants with stock OR parent product allows preorder
        if ($request->has('for_order')) {
            if (!$product->allow_preorder) {
                // If parent product doesn't allow preorder, only show variants with stock
                $query->whereHas('inventoryBalances', function ($q) {
                    $q->where('qty_on_hand', '>', 0);
                });
            }
            // If parent product allows preorder, show all variants (no filter)
        }

        $variants = $query->get()
            ->map(function ($variant) {
                $variantData = [
                    'id' => $variant->id, // IMPORTANT: Include variant ID
                    'sku' => $variant->sku,
                    'variant_attributes' => $variant->variant_attributes,
                    'cost_price' => $variant->cost_price,
                    'selling_price' => $variant->selling_price,
                    'weight_grams' => $variant->weight_grams,
                    'is_active' => $variant->is_active,
                    'variant_image_id' => $variant->variant_image_id,
                    'total_stock' => $variant->total_stock,
                ];

                // Include variant image data if exists
                if ($variant->variantImage) {
                    $variantData['variant_image'] = [
                        'id' => $variant->variantImage->id,
                        'url' => $variant->variantImage->url,
                        'path' => $variant->variantImage->path,
                        'filename' => $variant->variantImage->filename,
                    ];
                }

                return $variantData;
            });

        return response()->json([
            'variants' => $variants
        ]);
    }

    /**
     * Generate variant combinations from variant groups.
     */
    public function generateCombinations(Request $request, Product $product)
    {
        $validated = $request->validate([
            'variant_groups' => 'required|array',
            'variant_groups.*.name' => 'required|string',
            'variant_groups.*.options' => 'required|array|min:1',
        ]);

        $combinations = $this->variantGenerator->generateCombinations($validated['variant_groups']);

        // Add SKU preview for each combination
        $combinationsWithSku = array_map(function ($combo, $index) use ($product) {
            return [
                'variant_attributes' => $combo,
                'sku' => $this->variantGenerator->generateVariantSku($product->sku, $index),
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'weight_grams' => $product->weight_grams,
            ];
        }, $combinations, array_keys($combinations));

        return response()->json([
            'combinations' => $combinationsWithSku,
            'total' => count($combinationsWithSku),
        ]);
    }

    /**
     * Apply bulk pricing to variants.
     */
    public function applyBulkPricing(Request $request)
    {
        $validated = $request->validate([
            'variants' => 'required|array',
            'bulk_rules' => 'required|array',
            'bulk_rules.*.attribute' => 'required|string',
            'bulk_rules.*.value' => 'required|string',
            'bulk_rules.*.cost_price' => 'nullable|numeric',
            'bulk_rules.*.selling_price' => 'nullable|numeric',
            'bulk_rules.*.weight_grams' => 'nullable|integer',
        ]);

        $updatedVariants = $this->bulkPricing->applyBulkPricing(
            $validated['variants'],
            $validated['bulk_rules']
        );

        return response()->json([
            'variants' => $updatedVariants,
        ]);
    }

    /**
     * Save all variants for a product.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'variant_groups' => 'required|array',
            'variants' => 'required|array|min:1',
            'variants.*.variant_attributes' => 'required|array',
            'variants.*.cost_price' => 'required|numeric|min:0',
            'variants.*.selling_price' => 'required|numeric|min:0',
            'variants.*.weight_grams' => 'nullable|integer|min:0',
            'variants.*.featured_media_id' => 'nullable|exists:media,id',
        ]);

        return DB::transaction(function () use ($product, $validated) {
            // Update product to enable variants
            $product->update([
                'has_variants' => true,
                'variant_groups' => $validated['variant_groups'],
            ]);

            // Delete existing variants
            $product->variants()->delete();

            // Create new variants
            foreach ($validated['variants'] as $index => $variantData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $this->variantGenerator->generateVariantSku($product->sku, $index),
                    'variant_attributes' => $variantData['variant_attributes'],
                    'cost_price' => $variantData['cost_price'],
                    'selling_price' => $variantData['selling_price'],
                    'weight_grams' => $variantData['weight_grams'] ?? null,
                    'is_active' => true,
                    'featured_media_id' => $variantData['featured_media_id'] ?? null,
                ]);
            }

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Variasi produk berhasil disimpan.');
        });
    }

    /**
     * Update existing variants.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'variant_groups' => 'required|array',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.variant_attributes' => 'required|array',
            'variants.*.cost_price' => 'required|numeric|min:0',
            'variants.*.selling_price' => 'required|numeric|min:0',
            'variants.*.weight_grams' => 'nullable|integer|min:0',
            'variants.*.is_active' => 'boolean',
            'variants.*.featured_media_id' => 'nullable|exists:media,id',
        ]);

        return DB::transaction(function () use ($product, $validated) {
            // Update product variant groups
            $product->update([
                'variant_groups' => $validated['variant_groups'],
            ]);

            $existingIds = [];

            foreach ($validated['variants'] as $index => $variantData) {
                if (!empty($variantData['id'])) {
                    // Update existing variant
                    $variant = ProductVariant::findOrFail($variantData['id']);
                    $variant->update([
                        'variant_attributes' => $variantData['variant_attributes'],
                        'cost_price' => $variantData['cost_price'],
                        'selling_price' => $variantData['selling_price'],
                        'weight_grams' => $variantData['weight_grams'] ?? null,
                        'is_active' => $variantData['is_active'] ?? true,
                        'featured_media_id' => $variantData['featured_media_id'] ?? null,
                    ]);
                    $existingIds[] = $variant->id;
                } else {
                    // Create new variant
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $this->variantGenerator->generateVariantSku($product->sku, $index),
                        'variant_attributes' => $variantData['variant_attributes'],
                        'cost_price' => $variantData['cost_price'],
                        'selling_price' => $variantData['selling_price'],
                        'weight_grams' => $variantData['weight_grams'] ?? null,
                        'is_active' => $variantData['is_active'] ?? true,
                        'featured_media_id' => $variantData['featured_media_id'] ?? null,
                    ]);
                    $existingIds[] = $variant->id;
                }
            }

            // Soft delete removed variants (deactivate if has transactions)
            $product->variants()->whereNotIn('id', $existingIds)->update(['is_active' => false]);

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Variasi produk berhasil diperbarui.');
        });
    }

    /**
     * Delete all variants and convert back to simple product.
     */
    public function destroy(Product $product)
    {
        return DB::transaction(function () use ($product) {
            // Check if any variant has transactions
            $hasTransactions = $product->variants()
                ->whereHas('orderItems')
                ->orWhereHas('purchaseItems')
                ->exists();

            if ($hasTransactions) {
                return back()->with('error', 'Tidak dapat menghapus variasi karena sudah ada transaksi.');
            }

            // Delete all variants
            $product->variants()->delete();

            // Convert back to simple product
            $product->update([
                'has_variants' => false,
                'variant_groups' => null,
            ]);

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Semua variasi berhasil dihapus. Produk kembali menjadi produk simple.');
        });
    }

    /**
     * Toggle variant active status.
     */
    public function toggleActive(ProductVariant $variant)
    {
        $variant->update(['is_active' => !$variant->is_active]);

        return back()->with('success', 'Status variasi berhasil diubah.');
    }
}
