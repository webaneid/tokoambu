<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionBenefit;
use App\Models\PromotionTarget;
use App\Models\AiIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::query()
            ->withCount('targets')
            ->with(['bundle.items']);

        if ($search = $request->query('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $promotions = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('promotions.index', compact('promotions'));
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'flash_sale');
        $products = Product::where('is_active', true)
            ->with(['variants' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();
        $products = $this->filterPromoProducts($products);

        $aiIntegration = AiIntegration::where('provider', 'gemini')
            ->where('is_enabled', true)
            ->first();
        $aiEnabled = (bool) $aiIntegration;

        return view('promotions.create', compact('type', 'products', 'aiEnabled'));
    }

    public function show(Promotion $promotion)
    {
        $promotion->load(['targets', 'benefits', 'coupon', 'bundle.items.product', 'bundle.items.productVariant.product', 'bundle.featuredMedia']);

        $productTargetIds = $promotion->targets->where('target_type', 'product')->pluck('target_id')->unique();
        $variantTargetIds = $promotion->targets->where('target_type', 'variant')->pluck('target_id')->unique();

        $targetProducts = Product::whereIn('id', $productTargetIds)->get()->keyBy('id');
        $targetVariants = ProductVariant::with('product')
            ->whereIn('id', $variantTargetIds)
            ->get()
            ->keyBy('id');

        return view('promotions.show', compact('promotion', 'targetProducts', 'targetVariants'));
    }

    public function edit(Promotion $promotion)
    {
        $promotion->load(['targets', 'benefits', 'coupon', 'bundle.items.product', 'bundle.items.productVariant.product', 'bundle.featuredMedia']);
        $productTargetIds = $promotion->targets->where('target_type', 'product')->pluck('target_id')->unique();
        $variantTargetIds = $promotion->targets->where('target_type', 'variant')->pluck('target_id')->unique();
        $targetProducts = Product::whereIn('id', $productTargetIds)->get()->keyBy('id');
        $targetVariants = ProductVariant::with('product')
            ->whereIn('id', $variantTargetIds)
            ->get()
            ->keyBy('id');
        $products = Product::where('is_active', true)
            ->with(['variants' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();
        $products = $this->filterPromoProducts($products);

        $aiIntegration = AiIntegration::where('provider', 'gemini')
            ->where('is_enabled', true)
            ->first();
        $aiEnabled = (bool) $aiIntegration;

        return view('promotions.edit', compact('promotion', 'products', 'targetProducts', 'targetVariants', 'aiEnabled'));
    }

    public function store(Request $request)
    {
        $type = $request->input('type');

        $validated = $request->validate([
            'type' => 'required|in:flash_sale,coupon,bundle,cart_rule',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,scheduled,active,ended,archived',
            'priority' => 'required|integer|min:0',
            'stackable' => 'nullable|boolean',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'targets' => $type === 'flash_sale' ? 'required|array|min:1' : 'nullable|array',
            'targets.*' => ['nullable', 'regex:/^(product|variant):\d+$/'],
            'benefit_type' => [Rule::requiredIf($type !== 'bundle'), 'in:percent_off,amount_off,fixed_price,free_shipping'],
            'benefit_value' => [Rule::requiredIf($type !== 'bundle'), 'numeric', 'min:0'],
            'max_discount' => 'nullable|numeric|min:0',
            'apply_scope' => [Rule::requiredIf($type !== 'bundle'), 'in:item,cart,shipping'],
            'min_qty' => 'nullable|integer|min:1',
            'max_qty' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'min_stock_threshold' => 'nullable|integer|min:0',
            'coupon_code' => ['exclude_unless:type,coupon', 'required', 'string', 'max:50', Rule::unique('coupons', 'code')],
            'coupon_global_limit' => 'exclude_unless:type,coupon|nullable|integer|min:1',
            'coupon_per_user_limit' => 'exclude_unless:type,coupon|nullable|integer|min:1',
            'coupon_min_order_amount' => 'exclude_unless:type,coupon|nullable|numeric|min:0',
            'coupon_first_purchase_only' => 'exclude_unless:type,coupon|nullable|boolean',
            'bundle_pricing_mode' => ['exclude_unless:type,bundle', 'required', 'in:fixed,percent_off,amount_off'],
            'bundle_price' => [Rule::requiredIf($type === 'bundle' && $request->input('bundle_pricing_mode') === 'fixed'), 'exclude_unless:type,bundle', 'nullable', 'numeric', 'min:0'],
            'bundle_discount_value' => [Rule::requiredIf($type === 'bundle' && in_array($request->input('bundle_pricing_mode'), ['percent_off', 'amount_off'], true)), 'exclude_unless:type,bundle', 'nullable', 'numeric', 'min:0'],
            'bundle_must_be_cheaper' => 'exclude_unless:type,bundle|nullable|boolean',
            'bundle_items' => 'exclude_unless:type,bundle|required|array|min:1',
            'bundle_items.*.target' => ['exclude_unless:type,bundle', 'required', 'regex:/^(product|variant):\d+$/'],
            'bundle_items.*.qty' => 'exclude_unless:type,bundle|required|integer|min:1',
            'bundle_featured_media_id' => [
                'exclude_unless:type,bundle',
                'nullable',
                Rule::exists('media', 'id')->where('type', 'product_photo'),
            ],
        ]);

        $rules = array_filter([
            'min_qty' => $validated['min_qty'] ?? null,
            'max_qty' => $validated['max_qty'] ?? null,
            'per_user_limit' => $validated['per_user_limit'] ?? null,
            'min_stock_threshold' => $validated['min_stock_threshold'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if ($validated['type'] === 'bundle') {
            $priceErrors = $this->validateBundlePricing($validated);
            if ($priceErrors) {
                return back()->withErrors($priceErrors)->withInput();
            }
        }

        $promotion = Promotion::create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'stackable' => (bool)($validated['stackable'] ?? false),
            'start_at' => $validated['start_at'] ?? null,
            'end_at' => $validated['end_at'] ?? null,
            'rules' => $rules ?: null,
            'created_by' => $request->user()?->id,
        ]);

        $targets = $validated['targets'] ?? [];
        foreach ($targets as $target) {
            [$type, $targetId] = explode(':', $target, 2);
            PromotionTarget::create([
                'promotion_id' => $promotion->id,
                'target_type' => $type,
                'target_id' => $targetId,
                'include' => true,
            ]);
        }

        if ($validated['type'] !== 'bundle') {
            PromotionBenefit::create([
                'promotion_id' => $promotion->id,
                'benefit_type' => $validated['benefit_type'],
                'value' => $validated['benefit_value'],
                'max_discount' => $validated['max_discount'] ?? null,
                'apply_scope' => $validated['apply_scope'],
            ]);
        }

        if ($validated['type'] === 'coupon') {
            Coupon::create([
                'promotion_id' => $promotion->id,
                'code' => strtoupper($validated['coupon_code']),
                'global_limit' => $validated['coupon_global_limit'] ?? null,
                'per_user_limit' => $validated['coupon_per_user_limit'] ?? null,
                'min_order_amount' => $validated['coupon_min_order_amount'] ?? null,
                'first_purchase_only' => (bool)($validated['coupon_first_purchase_only'] ?? false),
            ]);
        }

        if ($validated['type'] === 'bundle') {
            $bundle = Bundle::create([
                'promotion_id' => $promotion->id,
                'pricing_mode' => $validated['bundle_pricing_mode'],
                'bundle_price' => $validated['bundle_pricing_mode'] === 'fixed' ? ($validated['bundle_price'] ?? null) : null,
                'discount_value' => in_array($validated['bundle_pricing_mode'], ['percent_off', 'amount_off'], true)
                    ? ($validated['bundle_discount_value'] ?? null)
                    : null,
                'must_be_cheaper' => (bool)($validated['bundle_must_be_cheaper'] ?? true),
                'compare_basis' => 'sum_items',
                'featured_media_id' => $validated['bundle_featured_media_id'] ?? null,
            ]);

            foreach ($validated['bundle_items'] as $item) {
                [$targetType, $targetId] = explode(':', $item['target'], 2);
                $productId = $targetType === 'variant'
                    ? ProductVariant::whereKey($targetId)->value('product_id')
                    : $targetId;

                BundleItem::create([
                    'bundle_id' => $bundle->id,
                    'product_id' => $productId,
                    'product_variant_id' => $targetType === 'variant' ? $targetId : null,
                    'qty' => $item['qty'],
                ]);
            }
        }

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil dibuat.');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $type = $request->input('type');

        $validated = $request->validate([
            'type' => 'required|in:flash_sale,coupon,bundle,cart_rule',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,scheduled,active,ended,archived',
            'priority' => 'required|integer|min:0',
            'stackable' => 'nullable|boolean',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'targets' => $type === 'flash_sale' ? 'required|array|min:1' : 'nullable|array',
            'targets.*' => ['nullable', 'regex:/^(product|variant):\d+$/'],
            'benefit_type' => [Rule::requiredIf($type !== 'bundle'), 'in:percent_off,amount_off,fixed_price,free_shipping'],
            'benefit_value' => [Rule::requiredIf($type !== 'bundle'), 'numeric', 'min:0'],
            'max_discount' => 'nullable|numeric|min:0',
            'apply_scope' => [Rule::requiredIf($type !== 'bundle'), 'in:item,cart,shipping'],
            'min_qty' => 'nullable|integer|min:1',
            'max_qty' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'min_stock_threshold' => 'nullable|integer|min:0',
            'coupon_code' => [
                'exclude_unless:type,coupon',
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($promotion->coupon?->id),
            ],
            'coupon_global_limit' => 'exclude_unless:type,coupon|nullable|integer|min:1',
            'coupon_per_user_limit' => 'exclude_unless:type,coupon|nullable|integer|min:1',
            'coupon_min_order_amount' => 'exclude_unless:type,coupon|nullable|numeric|min:0',
            'coupon_first_purchase_only' => 'exclude_unless:type,coupon|nullable|boolean',
            'bundle_pricing_mode' => ['exclude_unless:type,bundle', 'required', 'in:fixed,percent_off,amount_off'],
            'bundle_price' => [Rule::requiredIf($type === 'bundle' && $request->input('bundle_pricing_mode') === 'fixed'), 'exclude_unless:type,bundle', 'nullable', 'numeric', 'min:0'],
            'bundle_discount_value' => [Rule::requiredIf($type === 'bundle' && in_array($request->input('bundle_pricing_mode'), ['percent_off', 'amount_off'], true)), 'exclude_unless:type,bundle', 'nullable', 'numeric', 'min:0'],
            'bundle_must_be_cheaper' => 'exclude_unless:type,bundle|nullable|boolean',
            'bundle_items' => 'exclude_unless:type,bundle|required|array|min:1',
            'bundle_items.*.target' => ['exclude_unless:type,bundle', 'required', 'regex:/^(product|variant):\d+$/'],
            'bundle_items.*.qty' => 'exclude_unless:type,bundle|required|integer|min:1',
            'bundle_featured_media_id' => [
                'exclude_unless:type,bundle',
                'nullable',
                Rule::exists('media', 'id')->where('type', 'product_photo'),
            ],
        ]);

        if ($validated['type'] === 'bundle') {
            $priceErrors = $this->validateBundlePricing($validated);
            if ($priceErrors) {
                return back()->withErrors($priceErrors)->withInput();
            }
        }

        $rules = array_filter([
            'min_qty' => $validated['min_qty'] ?? null,
            'max_qty' => $validated['max_qty'] ?? null,
            'per_user_limit' => $validated['per_user_limit'] ?? null,
            'min_stock_threshold' => $validated['min_stock_threshold'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        DB::transaction(function () use ($promotion, $validated, $rules) {
            $promotion->update([
                'type' => $validated['type'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'priority' => $validated['priority'],
                'stackable' => (bool)($validated['stackable'] ?? false),
                'start_at' => $validated['start_at'] ?? null,
                'end_at' => $validated['end_at'] ?? null,
                'rules' => $rules ?: null,
            ]);

            $promotion->targets()->delete();
            $targets = $validated['targets'] ?? [];
            foreach ($targets as $target) {
                [$type, $targetId] = explode(':', $target, 2);
                PromotionTarget::create([
                    'promotion_id' => $promotion->id,
                    'target_type' => $type,
                    'target_id' => $targetId,
                    'include' => true,
                ]);
            }

            $promotion->benefits()->delete();
            if ($validated['type'] !== 'bundle') {
                PromotionBenefit::create([
                    'promotion_id' => $promotion->id,
                    'benefit_type' => $validated['benefit_type'],
                    'value' => $validated['benefit_value'],
                    'max_discount' => $validated['max_discount'] ?? null,
                    'apply_scope' => $validated['apply_scope'],
                ]);
            }

            if ($validated['type'] === 'coupon') {
                $promotion->coupon()->updateOrCreate(
                    ['promotion_id' => $promotion->id],
                    [
                        'code' => strtoupper($validated['coupon_code']),
                        'global_limit' => $validated['coupon_global_limit'] ?? null,
                        'per_user_limit' => $validated['coupon_per_user_limit'] ?? null,
                        'min_order_amount' => $validated['coupon_min_order_amount'] ?? null,
                        'first_purchase_only' => (bool)($validated['coupon_first_purchase_only'] ?? false),
                    ]
                );
            } else {
                $promotion->coupon()?->delete();
            }

            if ($validated['type'] === 'bundle') {
                $bundle = $promotion->bundle()->updateOrCreate(
                    ['promotion_id' => $promotion->id],
                    [
                        'pricing_mode' => $validated['bundle_pricing_mode'],
                        'bundle_price' => $validated['bundle_pricing_mode'] === 'fixed' ? ($validated['bundle_price'] ?? null) : null,
                        'discount_value' => in_array($validated['bundle_pricing_mode'], ['percent_off', 'amount_off'], true)
                            ? ($validated['bundle_discount_value'] ?? null)
                            : null,
                        'must_be_cheaper' => (bool)($validated['bundle_must_be_cheaper'] ?? true),
                        'compare_basis' => 'sum_items',
                        'featured_media_id' => $validated['bundle_featured_media_id'] ?? null,
                    ]
                );

                $bundle->items()->delete();
                foreach ($validated['bundle_items'] as $item) {
                    [$targetType, $targetId] = explode(':', $item['target'], 2);
                    $productId = $targetType === 'variant'
                        ? ProductVariant::whereKey($targetId)->value('product_id')
                        : $targetId;

                    BundleItem::create([
                        'bundle_id' => $bundle->id,
                        'product_id' => $productId,
                        'product_variant_id' => $targetType === 'variant' ? $targetId : null,
                        'qty' => $item['qty'],
                    ]);
                }
            } else {
                $promotion->bundle()?->delete();
            }
        });

        return redirect()->route('promotions.show', $promotion)->with('success', 'Promo berhasil diperbarui.');
    }

    public function duplicate(Promotion $promotion)
    {
        $promotion->load(['targets', 'benefits', 'coupon', 'bundle.items']);

        $copy = $promotion->replicate([
            'status',
            'start_at',
            'end_at',
            'created_at',
            'updated_at',
        ]);
        $copy->name = $promotion->name . ' (Copy)';
        $copy->status = 'draft';
        $copy->start_at = null;
        $copy->end_at = null;
        $copy->created_by = request()->user()?->id;
        $copy->save();

        foreach ($promotion->targets as $target) {
            PromotionTarget::create([
                'promotion_id' => $copy->id,
                'target_type' => $target->target_type,
                'target_id' => $target->target_id,
                'include' => $target->include,
            ]);
        }

        foreach ($promotion->benefits as $benefit) {
            PromotionBenefit::create([
                'promotion_id' => $copy->id,
                'benefit_type' => $benefit->benefit_type,
                'value' => $benefit->value,
                'max_discount' => $benefit->max_discount,
                'apply_scope' => $benefit->apply_scope,
            ]);
        }

        if ($promotion->coupon) {
            $baseCode = strtoupper($promotion->coupon->code) . '-COPY';
            $code = strlen($baseCode) > 50 ? substr($baseCode, 0, 50) : $baseCode;
            if (Coupon::where('code', $code)->exists()) {
                $suffix = '-' . Str::upper(Str::random(4));
                $trimmed = substr(strtoupper($promotion->coupon->code), 0, 50 - strlen($suffix));
                $code = $trimmed . $suffix;
            }

            Coupon::create([
                'promotion_id' => $copy->id,
                'code' => $code,
                'global_limit' => $promotion->coupon->global_limit,
                'per_user_limit' => $promotion->coupon->per_user_limit,
                'min_order_amount' => $promotion->coupon->min_order_amount,
                'first_purchase_only' => $promotion->coupon->first_purchase_only,
            ]);
        }

        if ($promotion->bundle) {
            $bundleCopy = Bundle::create([
                'promotion_id' => $copy->id,
                'pricing_mode' => $promotion->bundle->pricing_mode,
                'bundle_price' => $promotion->bundle->bundle_price,
                'discount_value' => $promotion->bundle->discount_value,
                'must_be_cheaper' => $promotion->bundle->must_be_cheaper,
                'compare_basis' => $promotion->bundle->compare_basis,
                'featured_media_id' => $promotion->bundle->featured_media_id,
            ]);

            foreach ($promotion->bundle->items as $item) {
                BundleItem::create([
                    'bundle_id' => $bundleCopy->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'qty' => $item->qty,
                ]);
            }
        }

        return redirect()->route('promotions.edit', $copy)->with('success', 'Promo berhasil diduplikasi.');
    }

    private function validateBundlePricing(array $validated): array
    {
        $items = $validated['bundle_items'] ?? [];
        if (!$items) {
            return [];
        }

        $productIds = collect($items)
            ->pluck('target')
            ->filter()
            ->map(function ($target) {
                return explode(':', $target, 2);
            });
        $productTargetIds = $productIds->where(fn ($parts) => $parts[0] === 'product')->pluck(1)->unique();
        $variantTargetIds = $productIds->where(fn ($parts) => $parts[0] === 'variant')->pluck(1)->unique();

        $productPrices = Product::whereIn('id', $productTargetIds)->pluck('selling_price', 'id');
        $variantPrices = ProductVariant::whereIn('id', $variantTargetIds)->pluck('selling_price', 'id');

        $sum = 0;
        foreach ($items as $item) {
            [$targetType, $targetId] = explode(':', $item['target'], 2);
            $price = $targetType === 'variant'
                ? ($variantPrices[$targetId] ?? 0)
                : ($productPrices[$targetId] ?? 0);
            $sum += (float) $price * (int) $item['qty'];
        }

        if ($sum <= 0) {
            return ['bundle_items' => 'Total harga produk bundle tidak valid.'];
        }

        $pricingMode = $validated['bundle_pricing_mode'];
        $mustBeCheaper = (bool)($validated['bundle_must_be_cheaper'] ?? true);
        $effectivePrice = $sum;

        if ($pricingMode === 'fixed') {
            $bundlePrice = (float)($validated['bundle_price'] ?? 0);
            $effectivePrice = $bundlePrice;
        } elseif ($pricingMode === 'percent_off') {
            $discount = (float)($validated['bundle_discount_value'] ?? 0);
            if ($discount <= 0 || $discount >= 100) {
                return ['bundle_discount_value' => 'Diskon persen harus diisi antara 1 - 99.'];
            }
            $effectivePrice = $sum * (1 - ($discount / 100));
        } elseif ($pricingMode === 'amount_off') {
            $discount = (float)($validated['bundle_discount_value'] ?? 0);
            if ($discount <= 0) {
                return ['bundle_discount_value' => 'Diskon harus lebih besar dari 0.'];
            }
            $effectivePrice = $sum - $discount;
        }

        if ($mustBeCheaper && $effectivePrice >= $sum) {
            return ['bundle_price' => 'Harga bundle harus lebih murah dari total harga produk.'];
        }

        return [];
    }

    public function endNow(Promotion $promotion)
    {
        $promotion->update([
            'status' => 'ended',
            'end_at' => now(),
        ]);

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil diakhiri.');
    }

    public function archive(Promotion $promotion)
    {
        $promotion->update([
            'status' => 'archived',
        ]);

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil diarsipkan.');
    }

    private function filterPromoProducts($products)
    {
        return $products->filter(function ($product) {
            if ($product->allow_preorder) {
                return true;
            }

            if ($product->isSimpleProduct()) {
                return $product->qty_on_hand > 0;
            }

            return $product->variants->sum('total_stock') > 0;
        })->values();
    }
}
