<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\FlashSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerWishlistController extends Controller
{
    public function index(Request $request, FlashSaleService $flashSaleService): View
    {
        $customer = auth('customer')->user();

        $wishlists = Wishlist::with([
            'product.featuredMedia',
            'product.category',
            'product.variants' => function ($query) {
                $query->where('is_active', true);
            },
            'product.inventoryBalances',
            'product.preorderPeriods' => function ($query) {
                $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            },
        ])
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(12)
            ->appends($request->query());

        $productCollection = $wishlists->getCollection()
            ->pluck('product')
            ->filter()
            ->keyBy('id');

        $productIds = $productCollection->keys()->all();

        $reservedQuantities = collect();
        if (!empty($productIds)) {
            $reservedQuantities = \App\Models\Order::where('type', 'preorder')
                ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment', 'paid'])
                ->whereHas('items', function ($query) use ($productIds) {
                    $query->whereIn('product_id', $productIds);
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
        }

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

        return view('storefront.customer.wishlist.index', [
            'wishlists' => $wishlists,
            'flashSaleMap' => $flashSaleMap,
            'reservedQuantities' => $reservedQuantities,
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $customer = auth('customer')->user();
        $productId = (int) $validated['product_id'];

        $existing = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $added = false;
        } else {
            Wishlist::create([
                'customer_id' => $customer->id,
                'product_id' => $productId,
            ]);
            $added = true;
        }

        $count = Wishlist::where('customer_id', $customer->id)->count();

        return response()->json([
            'added' => $added,
            'count' => $count,
        ]);
    }
}
