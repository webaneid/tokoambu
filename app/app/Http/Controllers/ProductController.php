<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with('category')
            ->withCount('variants')
            ->addSelect([
                'preorder_backlog' => OrderItem::query()
                    ->selectRaw('COALESCE(SUM(quantity - preorder_allocated_qty), 0)')
                    ->whereColumn('order_items.product_id', 'products.id')
                    ->where('order_items.is_preorder', true),
            ]);

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $categoryId = $request->query('category_id');
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $status = $request->query('status');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $preorder = $request->query('preorder');
        if ($preorder === 'enabled') {
            $query->where('allow_preorder', true);
        } elseif ($preorder === 'disabled') {
            $query->where('allow_preorder', false);
        }

        $allowedSorts = [
            'sku',
            'name',
            'category',
            'cost_price',
            'selling_price',
            'profit',
            'weight_grams',
            'qty_on_hand',
            'preorder_backlog',
            'created_at',
        ];
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'sku';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        if ($sort === 'category') {
            $query->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
                ->select('products.*')
                ->orderBy('product_categories.name', $direction);
        } elseif ($sort === 'profit') {
            $query->orderByRaw('(selling_price - cost_price) '.strtoupper($direction));
        } else {
            $query->orderBy($sort, $direction);
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = \App\Models\ProductCategory::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $minMargin = (float) Setting::get('min_margin_percent', 20);
        $categories = \App\Models\ProductCategory::orderBy('name')->get();
        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        // Check AI availability
        $aiIntegration = \App\Models\AiIntegration::where('provider', 'gemini')
            ->where('is_enabled', true)
            ->first();
        $aiEnabled = (bool) $aiIntegration;

        return view('products.create', compact('minMargin', 'categories', 'suppliers', 'aiEnabled'));
    }

    public function store(Request $request)
    {
        $hasVariants = $request->boolean('has_variants');

        // Debug logging
        \Log::info('ProductController@store - has_variants:', ['has_variants' => $hasVariants]);
        \Log::info('ProductController@store - variants data:', ['variants' => $request->input('variants')]);

        $rules = [
            'sku' => 'required|string|unique:products',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'featured_media_id' => 'required|exists:media,id',
            'gallery_media_ids' => 'nullable|string',
            'allow_preorder' => 'nullable|boolean',
            'preorder_eta_date' => 'nullable|date',
            'custom_field_values' => 'nullable|json',
            'has_variants' => 'nullable|boolean',
            'variant_groups' => 'nullable|json',
            'variants' => 'nullable|json',
        ];

        // Only require cost/selling/weight for simple products
        if (! $hasVariants) {
            $rules['cost_price'] = 'required|numeric|min:0';
            $rules['selling_price'] = 'required|numeric|min:0';
            $rules['weight_grams'] = 'nullable|integer|min:0';
        } else {
            $rules['cost_price'] = 'nullable|numeric|min:0';
            $rules['selling_price'] = 'nullable|numeric|min:0';
            $rules['weight_grams'] = 'nullable|integer|min:0';
        }

        $validated = $request->validate($rules);

        $validated['allow_preorder'] = $request->boolean('allow_preorder');
        $validated['has_variants'] = $hasVariants;

        // Parse custom field values from JSON
        if (! empty($validated['custom_field_values'])) {
            $validated['custom_field_values'] = json_decode($validated['custom_field_values'], true);
        } else {
            $validated['custom_field_values'] = null;
        }

        // Parse variant groups
        if (! empty($validated['variant_groups'])) {
            $validated['variant_groups'] = json_decode($validated['variant_groups'], true);
        } else {
            $validated['variant_groups'] = null;
        }

        // Set default values for simple products
        if (! $hasVariants) {
            $validated['cost_price'] = $validated['cost_price'] ?? 0;
            $validated['selling_price'] = $validated['selling_price'] ?? 0;
            $validated['weight_grams'] = $validated['weight_grams'] ?? 0;
        } else {
            // For variant products, set to 0 (prices and weight are per variant)
            $validated['cost_price'] = 0;
            $validated['selling_price'] = 0;
            $validated['weight_grams'] = 0;
        }

        $product = Product::create($validated + [
            'is_active' => $request->boolean('is_active', true),
            'stock' => 0, // stok dikelola oleh inventory engine
        ]);

        // Link featured image to product
        if ($request->filled('featured_media_id')) {
            $featuredMedia = \App\Models\Media::find($request->featured_media_id);
            if ($featuredMedia && $featuredMedia->type === 'product_photo') {
                $featuredMedia->update(['product_id' => $product->id]);
            }
        }

        // Link gallery images to product and set order
        if ($request->filled('gallery_media_ids')) {
            $galleryIds = json_decode($request->gallery_media_ids, true);
            if (is_array($galleryIds) && count($galleryIds) > 0) {
                foreach ($galleryIds as $index => $mediaId) {
                    $media = \App\Models\Media::find($mediaId);
                    if ($media && $media->type === 'product_photo') {
                        $media->update([
                            'product_id' => $product->id,
                            'gallery_order' => $index,
                        ]);
                    }
                }
            }
        }

        // Create variants if has_variants is true
        if ($hasVariants && $request->filled('variants')) {
            \Log::info('Has variants and variants field is filled');
            $variantsData = json_decode($request->variants, true);
            \Log::info('Variants data decoded:', ['count' => is_array($variantsData) ? count($variantsData) : 0, 'data' => $variantsData]);

            if (is_array($variantsData) && count($variantsData) > 0) {
                foreach ($variantsData as $variantData) {
                    $variant = \App\Models\ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'] ?? '',
                        'variant_attributes' => $variantData['variant_attributes'] ?? [],
                        'cost_price' => $variantData['cost_price'] ?? 0,
                        'selling_price' => $variantData['selling_price'] ?? 0,
                        'weight_grams' => $variantData['weight_grams'] ?? 0,
                        'is_active' => true,
                        'variant_image_id' => $variantData['variant_image_id'] ?? null,
                    ]);
                    \Log::info('Created variant:', ['id' => $variant->id, 'sku' => $variant->sku, 'image_id' => $variant->variant_image_id]);
                }
            } else {
                \Log::warning('Variants data is not valid array or empty');
            }
        } else {
            \Log::info('Not creating variants', ['has_variants' => $hasVariants, 'variants_filled' => $request->filled('variants')]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Produk berhasil ditambahkan',
                'product' => $product,
            ], 201);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'supplier', 'supplierPrices.supplier', 'featuredMedia', 'galleryMedia', 'variants.inventoryBalances', 'variants.variantImage']);

        // For simple product: get balances directly
        // For variable product: balances are loaded via variants
        $balances = $product->isSimpleProduct()
            ? $product->inventoryBalances()->with('location.warehouse')->get()
            : collect();

        $supplierPrices = $product->supplierPrices()->with('supplier')->orderByDesc('last_purchase_at')->get();
        $salesHistory = OrderItem::query()
            ->select('order_items.*')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $product->id)
            ->with(['order.customer', 'order.shipment', 'productVariant'])
            ->orderByDesc('orders.created_at')
            ->get();
        $preorderBacklog = OrderItem::query()
            ->where('product_id', $product->id)
            ->where('is_preorder', true)
            ->selectRaw('COALESCE(SUM(quantity - preorder_allocated_qty), 0) as backlog')
            ->value('backlog') ?? 0;

        // Calculate reserved quantity from actual DP-paid orders
        $reservedQty = Order::where('type', 'preorder')
            ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment', 'paid'])
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->with('items')
            ->get()
            ->sum(function ($order) use ($product) {
                return $order->items->where('product_id', $product->id)->sum('quantity');
            });

        return view('products.show', compact('product', 'balances', 'supplierPrices', 'salesHistory', 'preorderBacklog', 'reservedQty'));
    }

    public function edit(Product $product)
    {
        $product->load(['category', 'featuredMedia', 'galleryMedia']);
        $minMargin = (float) Setting::get('min_margin_percent', 20);
        $categoryCustomFields = $product->category->custom_fields ?? [];

        // Check AI availability
        $aiIntegration = \App\Models\AiIntegration::where('provider', 'gemini')
            ->where('is_enabled', true)
            ->first();
        $aiEnabled = (bool) $aiIntegration;

        return view('products.edit', compact('product', 'minMargin', 'categoryCustomFields', 'aiEnabled'));
    }

    public function update(Request $request, Product $product)
    {
        // Auto-detect has_variants from variants data (since edit form doesn't have checkbox)
        $variantsData = $request->input('variants');
        $hasVariantsData = !empty($variantsData) && $variantsData !== '[]';
        $hasVariants = $hasVariantsData || $request->boolean('has_variants');

        // Debug logging
        \Log::info('ProductController@update - has_variants:', ['has_variants' => $hasVariants, 'detected_from_data' => $hasVariantsData]);
        \Log::info('ProductController@update - variants data:', ['variants' => $variantsData]);

        $rules = [
            'sku' => 'required|string|unique:products,sku,'.$product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'is_active' => 'nullable|boolean',
            'featured_media_id' => 'nullable|exists:media,id',
            'gallery_media_ids' => 'nullable|string',
            'allow_preorder' => 'nullable|boolean',
            'preorder_eta_date' => 'nullable|date',
            'custom_field_values' => 'nullable|json',
            'has_variants' => 'nullable|boolean',
            'variant_groups' => 'nullable|json',
            'variants' => 'nullable|json',
        ];

        // Only require cost/selling/weight for simple products
        if (! $hasVariants) {
            $rules['cost_price'] = 'required|numeric|min:0';
            $rules['selling_price'] = 'required|numeric|min:0';
            $rules['weight_grams'] = 'nullable|integer|min:0';
        } else {
            $rules['cost_price'] = 'nullable|numeric|min:0';
            $rules['selling_price'] = 'nullable|numeric|min:0';
            $rules['weight_grams'] = 'nullable|integer|min:0';
        }

        $validated = $request->validate($rules);

        $validated['allow_preorder'] = $request->boolean('allow_preorder');
        $validated['has_variants'] = $hasVariants;

        // Parse custom field values from JSON
        if (! empty($validated['custom_field_values'])) {
            $validated['custom_field_values'] = json_decode($validated['custom_field_values'], true);
        } else {
            $validated['custom_field_values'] = null;
        }

        // Parse variant groups
        if (! empty($validated['variant_groups'])) {
            $validated['variant_groups'] = json_decode($validated['variant_groups'], true);
        } else {
            $validated['variant_groups'] = null;
        }

        // Set prices based on product type
        if (! $hasVariants) {
            $validated['cost_price'] = $validated['cost_price'] ?? $product->cost_price;
            $validated['selling_price'] = $validated['selling_price'] ?? $product->selling_price;
            $validated['weight_grams'] = $validated['weight_grams'] ?? $product->weight_grams ?? 0;
        } else {
            // For variant products, set to 0 (prices and weight are per variant)
            $validated['cost_price'] = 0;
            $validated['selling_price'] = 0;
            $validated['weight_grams'] = 0;
        }

        $product->update($validated);

        // Link featured image to product
        if ($request->filled('featured_media_id')) {
            $featuredMedia = \App\Models\Media::find($request->featured_media_id);
            if ($featuredMedia && $featuredMedia->type === 'product_photo') {
                $featuredMedia->update(['product_id' => $product->id]);
            }
        }

        // Debug gallery IDs
        \Log::info('Gallery update - featured_media_id:', ['id' => $request->featured_media_id]);
        \Log::info('Gallery update - gallery_media_ids:', ['ids' => $request->gallery_media_ids]);

        // Only update gallery if gallery_media_ids is provided
        if ($request->has('gallery_media_ids')) {
            // Clear existing gallery links (but don't delete media)
            \App\Models\Media::where('product_id', $product->id)
                ->where('type', 'product_photo')
                ->where('id', '!=', $request->featured_media_id)
                ->update(['product_id' => null, 'gallery_order' => 0]);

            // Link new gallery images to product and set order
            if ($request->filled('gallery_media_ids')) {
                $galleryIds = json_decode($request->gallery_media_ids, true);
                \Log::info('Gallery IDs decoded:', ['count' => is_array($galleryIds) ? count($galleryIds) : 0, 'ids' => $galleryIds]);
                if (is_array($galleryIds) && count($galleryIds) > 0) {
                    foreach ($galleryIds as $index => $mediaId) {
                        $media = \App\Models\Media::find($mediaId);
                        if ($media && $media->type === 'product_photo') {
                            $media->update([
                                'product_id' => $product->id,
                                'gallery_order' => $index,
                            ]);
                        }
                    }
                }
            }
            \Log::info('Gallery update completed');
        } else {
            \Log::info('Gallery media IDs not provided, keeping existing gallery');
        }

        // Sync variants
        if ($hasVariants && $request->filled('variants')) {
            \Log::info('Has variants and variants field is filled (UPDATE)');
            $variantsData = json_decode($request->variants, true);
            \Log::info('Variants data decoded (UPDATE):', ['count' => is_array($variantsData) ? count($variantsData) : 0, 'data' => $variantsData]);

            // Get existing variant SKUs
            $existingVariants = $product->variants()->pluck('sku', 'id')->toArray();
            $submittedSkus = collect($variantsData)->pluck('sku')->filter()->toArray();
            \Log::info('Existing vs submitted SKUs:', ['existing' => $existingVariants, 'submitted' => $submittedSkus]);

            // Delete variants that are no longer in the submission
            $deletedCount = $product->variants()
                ->whereNotIn('sku', $submittedSkus)
                ->delete();
            \Log::info('Deleted variants count:', ['count' => $deletedCount]);

            // Create or update variants
            if (is_array($variantsData) && count($variantsData) > 0) {
                foreach ($variantsData as $variantData) {
                    $sku = $variantData['sku'] ?? '';
                    if (empty($sku)) {
                        \Log::warning('Skipping variant with empty SKU');

                        continue;
                    }

                    // Try to find existing variant by SKU
                    $existingVariant = $product->variants()->where('sku', $sku)->first();

                    $variantPayload = [
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'variant_attributes' => $variantData['variant_attributes'] ?? [],
                        'cost_price' => $variantData['cost_price'] ?? 0,
                        'selling_price' => $variantData['selling_price'] ?? 0,
                        'weight_grams' => $variantData['weight_grams'] ?? 0,
                        'is_active' => $variantData['is_active'] ?? true,
                        'variant_image_id' => $variantData['variant_image_id'] ?? null,
                    ];

                    if ($existingVariant) {
                        // Update existing variant
                        $existingVariant->update($variantPayload);
                        \Log::info('Updated variant:', ['id' => $existingVariant->id, 'sku' => $sku, 'image_id' => $variantData['variant_image_id'] ?? null]);
                    } else {
                        // Create new variant
                        $variant = \App\Models\ProductVariant::create($variantPayload);
                        \Log::info('Created new variant:', ['id' => $variant->id, 'sku' => $sku, 'image_id' => $variant->variant_image_id]);
                    }
                }
            } else {
                \Log::warning('Variants data is not valid array or empty (UPDATE)');
            }
        } else {
            \Log::info('Not syncing variants (UPDATE)', ['has_variants' => $hasVariants, 'variants_filled' => $request->filled('variants')]);
        }

        // If switching from variant to simple, delete all variants
        if (! $hasVariants) {
            $deletedAll = $product->variants()->delete();
            \Log::info('Switched to simple product, deleted all variants:', ['count' => $deletedAll]);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }
}
