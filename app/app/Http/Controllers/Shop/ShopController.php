<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use App\Services\BundlePricingService;
use App\Services\FlashSaleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ShopController extends Controller
{
    /**
     * Display product listing page with pagination, search, and filtering
     */
    public function index(Request $request, FlashSaleService $flashSaleService, BundlePricingService $bundlePricingService): View
    {
        $heroBanners = collect(json_decode(Setting::get('storefront_banners', '[]'), true))
            ->filter(fn ($banner) => ($banner['is_active'] ?? true))
            ->values();

        // Start query builder
        $query = Product::with([
            'featuredMedia',
            'category',
            'variants' => function($q) {
                $q->where('is_active', true);
            },
            'inventoryBalances',
            'preorderPeriods' => function($q) {
                $q->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            }
        ])->where('is_active', true);

        // Search by name, SKU, or description
        if ($request->filled('q')) {
            $searchTerm = $request->input('q');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Category filter disabled for storefront browse (categories are display-only)

        // Sorting
        $sort = $request->input('sort', 'newest');
        match($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderBy('quantity', 'desc'),
            default => $query->orderBy('created_at', 'desc'), // newest
        };

        // Price range filter (optional)
        if ($request->filled('price_min') && is_numeric($request->input('price_min'))) {
            $query->where('price', '>=', $request->input('price_min'));
        }
        if ($request->filled('price_max') && is_numeric($request->input('price_max'))) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        // Get paginated products
        $products = $query->paginate(20)->appends($request->query());

        // Calculate reserved quantities for all products in one query
        $productIds = $products->pluck('id')->toArray();
        $reservedQuantities = \App\Models\Order::where('type', 'preorder')
            ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment', 'paid'])
            ->whereHas('items', function($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            })
            ->with('items')
            ->get()
            ->flatMap(function($order) {
                return $order->items;
            })
            ->groupBy('product_id')
            ->map(function($items) {
                return $items->sum('quantity');
            });

        // Get all categories for filter dropdown
        $categories = ProductCategory::orderBy('name')->get();

        $productCollection = $products->getCollection()->keyBy('id');
        $variantCollection = $productCollection->pluck('variants')->flatten()->keyBy('id');

        $productItems = $productCollection->map(fn ($product) => [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'quantity' => 1,
            'unit_price' => (float) $product->selling_price,
        ])->values()->all();

        $variantItems = $variantCollection->map(fn ($variant) => [
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => (float) $variant->selling_price,
        ])->values()->all();

        $pricedProductItems = collect($flashSaleService->applyToItems($productItems, $productCollection, $variantCollection))
            ->keyBy('product_id');
        $pricedVariantItems = collect($flashSaleService->applyToItems($variantItems, $productCollection, $variantCollection))
            ->groupBy('product_id');

        $flashSaleMap = $productCollection->mapWithKeys(function ($product) use ($pricedProductItems, $pricedVariantItems) {
            $productItem = $pricedProductItems->get($product->id);

            if ($productItem && isset($productItem['original_price']) && $productItem['original_price'] > $productItem['unit_price']) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $productItem['original_price'],
                        'sale_price' => (float) $productItem['unit_price'],
                    ],
                ];
            }

            $variantItems = $pricedVariantItems->get($product->id, collect());
            $discountedVariant = $variantItems
                ->filter(fn ($item) => isset($item['original_price']) && $item['original_price'] > $item['unit_price'])
                ->sortBy('unit_price')
                ->first();

            if ($discountedVariant) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $discountedVariant['original_price'],
                        'sale_price' => (float) $discountedVariant['unit_price'],
                    ],
                ];
            }

            return [
                $product->id => [
                    'has_flash_sale' => false,
                ],
            ];
        })->all();

        $flashSaleProducts = $productCollection->filter(function ($product) use ($flashSaleMap) {
            return $flashSaleMap[$product->id]['has_flash_sale'] ?? false;
        })->values();

        $perPage = 12;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $flashSalePage = new LengthAwarePaginator(
            $flashSaleProducts->forPage($page, $perPage)->values(),
            $flashSaleProducts->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $flashSaleMaxDiscount = Promotion::query()
            ->where('type', 'flash_sale')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with('benefits')
            ->get()
            ->flatMap(fn ($promo) => $promo->benefits)
            ->where('benefit_type', 'percent_off')
            ->max('value');

        $flashSaleEndsAt = Promotion::query()
            ->where('type', 'flash_sale')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->whereNotNull('end_at')
            ->where('end_at', '>=', now())
            ->min('end_at');

        $flashSaleTitle = Promotion::query()
            ->where('type', 'flash_sale')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->orderByDesc('start_at')
            ->value('name');

        $bundlePromotions = Promotion::query()
            ->where('type', 'bundle')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with([
                'bundle.featuredMedia',
                'bundle.items.product.featuredMedia',
                'bundle.items.productVariant',
            ])
            ->get();

        $bundleCards = $bundlePromotions->map(function ($promotion) use ($bundlePricingService) {
            $bundle = $promotion->bundle;
            if (! $bundle || $bundle->items->isEmpty()) {
                return null;
            }

            $pricing = $bundlePricingService->calculate($bundle);
            $firstItem = $bundle->items->first();
            $image = $bundle->featuredMedia
                ?? $firstItem->productVariant?->featuredMedia
                ?? $firstItem->product?->featuredMedia;

            return [
                'promotion_id' => $promotion->id,
                'bundle_id' => $bundle->id,
                'name' => $promotion->name,
                'description' => $promotion->description,
                'image' => $image,
                'original_total' => $pricing['original_total'],
                'bundle_price' => $pricing['bundle_price'],
                'discount_percent' => $pricing['discount_percent'],
            ];
        })->filter()->values();

        $bundleMaxDiscount = $bundleCards->max('discount_percent');
        $bundleEndsAt = $bundlePromotions->min('end_at');
        $bundleQueue = $bundleCards->values();
        $bundleIndex = 0;
        $mixedItems = collect();

        foreach ($products as $index => $product) {
            $mixedItems->push([
                'type' => 'product',
                'data' => $product,
            ]);

            if (($index + 1) % 10 === 0 && $bundleIndex < $bundleQueue->count()) {
                $mixedItems->push([
                    'type' => 'bundle',
                    'data' => $bundleQueue[$bundleIndex],
                ]);
                $bundleIndex++;
            }
        }

        while ($bundleIndex < $bundleQueue->count()) {
            $mixedItems->push([
                'type' => 'bundle',
                'data' => $bundleQueue[$bundleIndex],
            ]);
            $bundleIndex++;
        }

        return view('storefront.shop.index-mobile', [
            'heroBanners' => $heroBanners,
            'products' => $products,
            'categories' => $categories,
            'reservedQuantities' => $reservedQuantities,
            'flashSaleMap' => $flashSaleMap,
            'flashSaleProducts' => $flashSaleProducts,
            'flashSaleMaxDiscount' => $flashSaleMaxDiscount,
            'flashSaleEndsAt' => $flashSaleEndsAt,
            'flashSaleTitle' => $flashSaleTitle,
            'bundleCards' => $bundleCards,
            'bundleMaxDiscount' => $bundleMaxDiscount,
            'bundleEndsAt' => $bundleEndsAt,
            'mixedItems' => $mixedItems,
            'bundleInsertMode' => true,
            'searchQuery' => $request->input('q'),
            'selectedCategory' => $request->input('category'),
            'selectedSort' => $sort,
            'priceMin' => $request->input('price_min'),
            'priceMax' => $request->input('price_max'),
        ]);
    }

    public function bundleShow(Promotion $promotion, BundlePricingService $bundlePricingService): View
    {
        if ($promotion->type !== 'bundle') {
            abort(404);
        }

        $promotion->load([
            'bundle.featuredMedia',
            'bundle.items.product.featuredMedia',
            'bundle.items.productVariant',
        ]);

        $bundle = $promotion->bundle;
        if (! $bundle || $bundle->items->isEmpty()) {
            abort(404);
        }

        $pricing = $bundlePricingService->calculate($bundle);
        $firstItem = $bundle->items->first();
        $image = $bundle->featuredMedia
            ?? $firstItem->productVariant?->featuredMedia
            ?? $firstItem->product?->featuredMedia;

        return view('storefront.shop.bundle', [
            'promotion' => $promotion,
            'bundle' => $bundle,
            'pricing' => $pricing,
            'image' => $image,
        ]);
    }

    /**
     * Display all products page (mobile-first) with category chips and infinite scroll
     */
    public function all(Request $request, FlashSaleService $flashSaleService): View
    {
        $products = Product::with([
            'featuredMedia',
            'category',
            'variants' => function($q) {
                $q->where('is_active', true);
            },
            'inventoryBalances',
            'preorderPeriods' => function($q) {
                $q->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            }
        ])->where('is_active', true)
          ->orderBy('created_at', 'desc')
          ->paginate(12);

        $productCollection = $products->getCollection()->keyBy('id');
        $variantCollection = $productCollection->pluck('variants')->flatten()->keyBy('id');

        $productItems = $productCollection->map(fn ($product) => [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'quantity' => 1,
            'unit_price' => (float) $product->selling_price,
        ])->values()->all();

        $variantItems = $variantCollection->map(fn ($variant) => [
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => (float) $variant->selling_price,
        ])->values()->all();

        $pricedProductItems = collect($flashSaleService->applyToItems($productItems, $productCollection, $variantCollection))
            ->keyBy('product_id');
        $pricedVariantItems = collect($flashSaleService->applyToItems($variantItems, $productCollection, $variantCollection))
            ->groupBy('product_id');

        $flashSaleMap = $productCollection->mapWithKeys(function ($product) use ($pricedProductItems, $pricedVariantItems) {
            $productItem = $pricedProductItems->get($product->id);

            if ($productItem && isset($productItem['original_price']) && $productItem['original_price'] > $productItem['unit_price']) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $productItem['original_price'],
                        'sale_price' => (float) $productItem['unit_price'],
                    ],
                ];
            }

            $variantItems = $pricedVariantItems->get($product->id, collect());
            $discountedVariant = $variantItems
                ->filter(fn ($item) => isset($item['original_price']) && $item['original_price'] > $item['unit_price'])
                ->sortBy('unit_price')
                ->first();

            if ($discountedVariant) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $discountedVariant['original_price'],
                        'sale_price' => (float) $discountedVariant['unit_price'],
                    ],
                ];
            }

            return [
                $product->id => [
                    'has_flash_sale' => false,
                ],
            ];
        })->all();

        $productIds = $products->getCollection()->pluck('id')->toArray();
        $reservedQuantities = \App\Models\Order::where('type', 'preorder')
            ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment', 'paid'])
            ->whereHas('items', function($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            })
            ->with('items')
            ->get()
            ->flatMap(function($order) {
                return $order->items;
            })
            ->groupBy('product_id')
            ->map(function($items) {
                return $items->sum('quantity');
            });

        $categories = ProductCategory::orderBy('name')->get();

        return view('storefront.shop.all', [
            'products' => $products,
            'categories' => $categories,
            'reservedQuantities' => $reservedQuantities,
            'flashSaleMap' => $flashSaleMap,
        ]);
    }

    /**
     * Display product detail page
     */
    public function show(string $slug): View
    {
        // Get product by slug or ID if slug is empty
        $product = Product::with([
                'featuredMedia',
                'galleryMedia',
                'category',
                'variants' => function($q) {
                    $q->where('is_active', true)->with(['variantImage', 'inventoryBalances']);
                },
                'inventoryBalances',
                'preorderPeriods' => function($q) {
                    $q->where('status', 'active')
                      ->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
                }
            ])
            ->where(function($query) use ($slug) {
                $query->where('slug', $slug)
                      ->orWhere('id', $slug);
            })
            ->where('is_active', true)
            ->firstOrFail();

        // Calculate reserved quantity for this product
        $reservedQty = \App\Models\Order::where('type', 'preorder')
            ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment', 'paid'])
            ->whereHas('items', function($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->with('items')
            ->get()
            ->sum(function($order) use ($product) {
                return $order->items->where('product_id', $product->id)->sum('quantity');
            });

        // Calculate reserved quantities per variant (for variant products)
        $variantReservedQty = [];
        if ($product->has_variants && $product->variants->count() > 0) {
            $variantIds = $product->variants->pluck('id')->toArray();
            $variantReservedQty = \App\Models\Order::where('type', 'preorder')
                ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment', 'paid'])
                ->whereHas('items', function($q) use ($variantIds) {
                    $q->whereIn('variant_id', $variantIds);
                })
                ->with('items')
                ->get()
                ->flatMap(function($order) {
                    return $order->items;
                })
                ->groupBy('variant_id')
                ->map(function($items) {
                    return $items->sum('quantity');
                })
                ->toArray();
        }

        // Calculate total sold (from completed orders)
        $totalSold = \App\Models\OrderItem::whereHas('order', function($q) {
                $q->whereIn('status', ['shipped', 'done']);
            })
            ->where('product_id', $product->id)
            ->sum('quantity');

        // Get related products (same category)
        $relatedProducts = Product::with(['featuredMedia', 'variants' => function($q) {
                $q->where('is_active', true);
            }])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('storefront.shop.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'reservedQty' => $reservedQty,
            'variantReservedQty' => $variantReservedQty,
            'totalSold' => $totalSold,
        ]);
    }

    /**
     * Display flash sale listing page
     */
    public function flashSale(Request $request, FlashSaleService $flashSaleService): View
    {
        $products = Product::with([
            'featuredMedia',
            'category',
            'variants' => function($q) {
                $q->where('is_active', true);
            },
            'inventoryBalances',
            'preorderPeriods' => function($q) {
                $q->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            }
        ])->where('is_active', true)
          ->orderBy('created_at', 'desc')
          ->get();

        $productCollection = $products->keyBy('id');
        $variantCollection = $productCollection->pluck('variants')->flatten()->keyBy('id');

        $productItems = $productCollection->map(fn ($product) => [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'quantity' => 1,
            'unit_price' => (float) $product->selling_price,
        ])->values()->all();

        $variantItems = $variantCollection->map(fn ($variant) => [
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => (float) $variant->selling_price,
        ])->values()->all();

        $pricedProductItems = collect($flashSaleService->applyToItems($productItems, $productCollection, $variantCollection))
            ->keyBy('product_id');
        $pricedVariantItems = collect($flashSaleService->applyToItems($variantItems, $productCollection, $variantCollection))
            ->groupBy('product_id');

        $flashSaleMap = $productCollection->mapWithKeys(function ($product) use ($pricedProductItems, $pricedVariantItems) {
            $productItem = $pricedProductItems->get($product->id);

            if ($productItem && isset($productItem['original_price']) && $productItem['original_price'] > $productItem['unit_price']) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $productItem['original_price'],
                        'sale_price' => (float) $productItem['unit_price'],
                    ],
                ];
            }

            $variantItems = $pricedVariantItems->get($product->id, collect());
            $discountedVariant = $variantItems
                ->filter(fn ($item) => isset($item['original_price']) && $item['original_price'] > $item['unit_price'])
                ->sortBy('unit_price')
                ->first();

            if ($discountedVariant) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $discountedVariant['original_price'],
                        'sale_price' => (float) $discountedVariant['unit_price'],
                    ],
                ];
            }

            return [
                $product->id => [
                    'has_flash_sale' => false,
                ],
            ];
        })->all();

        $flashSaleProducts = $productCollection->filter(function ($product) use ($flashSaleMap) {
            return $flashSaleMap[$product->id]['has_flash_sale'] ?? false;
        })->values();

        $perPage = 12;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $flashSalePage = new LengthAwarePaginator(
            $flashSaleProducts->forPage($page, $perPage)->values(),
            $flashSaleProducts->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $flashSaleMaxDiscount = Promotion::query()
            ->where('type', 'flash_sale')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with('benefits')
            ->get()
            ->flatMap(fn ($promo) => $promo->benefits)
            ->where('benefit_type', 'percent_off')
            ->max('value');

        $flashSaleEndsAt = Promotion::query()
            ->where('type', 'flash_sale')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->whereNotNull('end_at')
            ->where('end_at', '>=', now())
            ->min('end_at');

        $productIds = $flashSalePage->getCollection()->pluck('id')->toArray();
        $reservedQuantities = \App\Models\Order::where('type', 'preorder')
            ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment', 'paid'])
            ->whereHas('items', function($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            })
            ->with('items')
            ->get()
            ->flatMap(function($order) {
                return $order->items;
            })
            ->groupBy('product_id')
            ->map(function($items) {
                return $items->sum('quantity');
            });

        return view('storefront.shop.flash-sale', [
            'products' => $flashSalePage,
            'flashSaleMap' => $flashSaleMap,
            'reservedQuantities' => $reservedQuantities,
            'flashSaleMaxDiscount' => $flashSaleMaxDiscount,
            'flashSaleEndsAt' => $flashSaleEndsAt,
        ]);
    }

    public function bundleSale(Request $request, BundlePricingService $bundlePricingService): View
    {
        $bundlePromotions = Promotion::query()
            ->where('type', 'bundle')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with([
                'bundle.featuredMedia',
                'bundle.items.product.featuredMedia',
                'bundle.items.productVariant',
            ])
            ->orderByDesc('start_at')
            ->paginate(10);

        $bundleCards = $bundlePromotions->getCollection()->map(function ($promotion) use ($bundlePricingService) {
            $bundle = $promotion->bundle;
            if (! $bundle || $bundle->items->isEmpty()) {
                return null;
            }

            $pricing = $bundlePricingService->calculate($bundle);
            $firstItem = $bundle->items->first();
            $image = $bundle->featuredMedia
                ?? $firstItem->productVariant?->featuredMedia
                ?? $firstItem->product?->featuredMedia;

            return [
                'promotion_id' => $promotion->id,
                'bundle_id' => $bundle->id,
                'name' => $promotion->name,
                'description' => $promotion->description,
                'image' => $image,
                'original_total' => $pricing['original_total'],
                'bundle_price' => $pricing['bundle_price'],
                'discount_percent' => $pricing['discount_percent'],
            ];
        })->filter()->values();

        $bundlePromotions->setCollection($bundleCards);

        $bundleStats = Promotion::query()
            ->where('type', 'bundle')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with([
                'bundle.featuredMedia',
                'bundle.items.product.featuredMedia',
                'bundle.items.productVariant',
            ])
            ->get();

        $bundleMaxDiscount = $bundleStats->map(function ($promotion) use ($bundlePricingService) {
            $bundle = $promotion->bundle;
            if (! $bundle || $bundle->items->isEmpty()) {
                return null;
            }

            $pricing = $bundlePricingService->calculate($bundle);
            return $pricing['discount_percent'];
        })->filter()->max();

        $bundleEndsAt = $bundleStats->min('end_at');

        return view('storefront.shop.bundle-sale', [
            'bundles' => $bundlePromotions,
            'bundleMaxDiscount' => $bundleMaxDiscount,
            'bundleEndsAt' => $bundleEndsAt,
        ]);
    }

    /**
     * Display search results page
     */
    public function search(Request $request, FlashSaleService $flashSaleService): View
    {
        $searchQuery = $request->input('q', '');

        $query = Product::with([
            'featuredMedia',
            'category',
            'variants' => function ($q) {
                $q->where('is_active', true);
            },
            'inventoryBalances',
            'preorderPeriods' => function ($q) {
                $q->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            },
        ])->where('is_active', true);

        // Search by name, SKU, or description
        if (!empty($searchQuery)) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('sku', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%");
            });
        }

        $query->orderBy('created_at', 'desc');
        $products = $query->paginate(20)->appends($request->query());

        $productIds = $products->pluck('id')->toArray();
        $reservedQuantities = \App\Models\Order::where('type', 'preorder')
            ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment', 'paid'])
            ->whereHas('items', function($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            })
            ->with('items')
            ->get()
            ->flatMap(function($order) {
                return $order->items;
            })
            ->groupBy('product_id')
            ->map(function($items) {
                return $items->sum('quantity');
            });

        $productCollection = $products->getCollection()->keyBy('id');
        $variantCollection = $productCollection->pluck('variants')->flatten()->keyBy('id');

        $productItems = $productCollection->map(fn ($product) => [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'quantity' => 1,
            'unit_price' => (float) $product->selling_price,
        ])->values()->all();

        $variantItems = $variantCollection->map(fn ($variant) => [
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => (float) $variant->selling_price,
        ])->values()->all();

        $pricedProductItems = collect($flashSaleService->applyToItems($productItems, $productCollection, $variantCollection))
            ->keyBy('product_id');
        $pricedVariantItems = collect($flashSaleService->applyToItems($variantItems, $productCollection, $variantCollection))
            ->groupBy('product_id');

        $flashSaleMap = $productCollection->mapWithKeys(function ($product) use ($pricedProductItems, $pricedVariantItems) {
            $productItem = $pricedProductItems->get($product->id);

            if ($productItem && isset($productItem['original_price']) && $productItem['original_price'] > $productItem['unit_price']) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $productItem['original_price'],
                        'sale_price' => (float) $productItem['unit_price'],
                    ],
                ];
            }

            $variantItems = $pricedVariantItems->get($product->id, collect());
            $discountedVariant = $variantItems
                ->filter(fn ($item) => isset($item['original_price']) && $item['original_price'] > $item['unit_price'])
                ->sortBy('unit_price')
                ->first();

            if ($discountedVariant) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $discountedVariant['original_price'],
                        'sale_price' => (float) $discountedVariant['unit_price'],
                    ],
                ];
            }

            return [$product->id => ['has_flash_sale' => false]];
        })->all();

        return view('storefront.shop.search', [
            'products' => $products,
            'reservedQuantities' => $reservedQuantities,
            'flashSaleMap' => $flashSaleMap,
            'searchQuery' => $searchQuery,
            'totalResults' => $products->total(),
        ]);
    }

    public function category(Request $request, ProductCategory $category, FlashSaleService $flashSaleService): View
    {
        $query = Product::with([
            'featuredMedia',
            'category',
            'variants' => function ($q) {
                $q->where('is_active', true);
            },
            'inventoryBalances',
            'preorderPeriods' => function ($q) {
                $q->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            },
        ])
            ->where('is_active', true)
            ->where('category_id', $category->id)
            ->orderBy('created_at', 'desc');

        $products = $query->paginate(20)->appends($request->query());

        $productIds = $products->pluck('id')->toArray();
        $reservedQuantities = \App\Models\Order::where('type', 'preorder')
            ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment', 'paid'])
            ->whereHas('items', function ($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            })
            ->with('items')
            ->get()
            ->flatMap(function ($order) {
                return $order->items;
            })
            ->groupBy('product_id')
            ->map(function ($items) {
                return $items->sum('quantity');
            });

        $productCollection = $products->getCollection()->keyBy('id');
        $variantCollection = $productCollection->pluck('variants')->flatten()->keyBy('id');

        $productItems = $productCollection->map(fn ($product) => [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'quantity' => 1,
            'unit_price' => (float) $product->selling_price,
        ])->values()->all();

        $variantItems = $variantCollection->map(fn ($variant) => [
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => (float) $variant->selling_price,
        ])->values()->all();

        $pricedProductItems = collect($flashSaleService->applyToItems($productItems, $productCollection, $variantCollection))
            ->keyBy('product_id');
        $pricedVariantItems = collect($flashSaleService->applyToItems($variantItems, $productCollection, $variantCollection))
            ->groupBy('product_id');

        $flashSaleMap = $productCollection->mapWithKeys(function ($product) use ($pricedProductItems, $pricedVariantItems) {
            $productItem = $pricedProductItems->get($product->id);

            if ($productItem && isset($productItem['original_price']) && $productItem['original_price'] > $productItem['unit_price']) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $productItem['original_price'],
                        'sale_price' => (float) $productItem['unit_price'],
                    ],
                ];
            }

            $variantItems = $pricedVariantItems->get($product->id, collect());
            $discountedVariant = $variantItems
                ->filter(fn ($item) => isset($item['original_price']) && $item['original_price'] > $item['unit_price'])
                ->sortBy('unit_price')
                ->first();

            if ($discountedVariant) {
                return [
                    $product->id => [
                        'has_flash_sale' => true,
                        'original_price' => (float) $discountedVariant['original_price'],
                        'sale_price' => (float) $discountedVariant['unit_price'],
                    ],
                ];
            }

            return [
                $product->id => [
                    'has_flash_sale' => false,
                ],
            ];
        })->all();

        return view('storefront.shop.category', [
            'category' => $category,
            'products' => $products,
            'reservedQuantities' => $reservedQuantities,
            'flashSaleMap' => $flashSaleMap,
        ]);
    }
}
