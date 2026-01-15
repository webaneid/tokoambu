<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\BundlePricingService;
use App\Services\FlashSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CouponService $couponService,
        protected BundlePricingService $bundlePricingService,
        protected FlashSaleService $flashSaleService
    ) {}

    /**
     * Display shopping cart page
     */
    public function index(): View
    {
        $pricing = $this->cartService->getPricingWithFlashSale(
            $this->flashSaleService,
            $this->bundlePricingService
        );
        $items = $pricing['items'];
        $subtotal = $pricing['subtotal'];
        $total = $pricing['total'];
        $count = $this->cartService->count();
        $couponCode = $this->cartService->getCouponCode();
        $couponResult = $this->couponService->applyCoupon(
            $couponCode,
            $items,
            $subtotal,
            auth('customer')->user(),
            $pricing['flash_sale_promotion_ids'],
            0.0
        );

        if ($couponCode && ! $couponResult['valid']) {
            $this->cartService->clearCouponCode();
            $couponCode = null;
        }

        return view('storefront.cart.index', [
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $couponResult['total'],
            'count' => $count,
            'couponCode' => $couponCode,
            'couponDiscount' => $couponResult['discount'],
            'couponMessage' => $couponResult['message'],
            'couponPromotion' => $couponResult['promotion'],
        ]);
    }

    /**
     * Add product to cart (API endpoint)
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        try {
            $product = Product::findOrFail($request->product_id);
            $variantId = $request->variant_id;

            // Add to cart
            $this->cartService->add(
                $product,
                $request->quantity,
                $variantId
            );

            $count = $this->cartService->count();

            return response()->json([
                'success' => true,
                'message' => $product->name . ' ditambahkan ke keranjang',
                'count' => $count,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add bundle promotion to cart (API endpoint)
     */
    public function storeBundle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bundle_id' => 'required|integer|exists:bundles,id',
            'quantity' => 'nullable|integer|min:1|max:99',
        ]);

        try {
            $bundle = \App\Models\Bundle::with(['promotion', 'items'])->findOrFail($validated['bundle_id']);
            if (! $bundle->promotion || $bundle->promotion->type !== 'bundle') {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo bundle tidak valid.',
                ], 422);
            }

            if ($bundle->promotion->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo bundle belum aktif.',
                ], 422);
            }

            $this->cartService->addBundle($bundle, (int) ($validated['quantity'] ?? 1));

            return response()->json([
                'success' => true,
                'message' => $bundle->promotion->name . ' ditambahkan ke keranjang',
                'count' => $this->cartService->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_item_id' => 'required|integer|exists:cart_items,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        try {
            $this->cartService->update(
                $validated['cart_item_id'],
                $validated['quantity']
            );

            $count = $this->cartService->count();
            $pricing = $this->cartService->getPricingWithFlashSale(
                $this->flashSaleService,
                $this->bundlePricingService
            );
            $couponResult = $this->couponService->applyCoupon(
                $this->cartService->getCouponCode(),
                $pricing['items'],
                $pricing['subtotal'],
                auth('customer')->user(),
                $pricing['flash_sale_promotion_ids'],
                0.0
            );
            if ($this->cartService->getCouponCode() && ! $couponResult['valid']) {
                $this->cartService->clearCouponCode();
            }

            return response()->json([
                'success' => true,
                'message' => 'Keranjang diperbarui',
                'count' => $count,
                'total' => $couponResult['total'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui keranjang',
            ], 500);
        }
    }

    /**
     * Remove item from cart by cart item ID
     */
    public function destroy(int $cartItemId): JsonResponse
    {
        try {
            $this->cartService->remove($cartItemId);

            $count = $this->cartService->count();
            $pricing = $this->cartService->getPricingWithFlashSale(
                $this->flashSaleService,
                $this->bundlePricingService
            );
            $couponResult = $this->couponService->applyCoupon(
                $this->cartService->getCouponCode(),
                $pricing['items'],
                $pricing['subtotal'],
                auth('customer')->user(),
                $pricing['flash_sale_promotion_ids'],
                0.0
            );
            if ($this->cartService->getCouponCode() && ! $couponResult['valid']) {
                $this->cartService->clearCouponCode();
            }

            return response()->json([
                'success' => true,
                'message' => 'Produk dihapus dari keranjang',
                'count' => $count,
                'total' => $couponResult['total'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk',
            ], 500);
        }
    }

    /**
     * Clear entire cart
     */
    public function clear(): RedirectResponse
    {
        $this->cartService->clear();
        $this->cartService->clearCouponCode();

        return redirect()->route('shop.index')
            ->with('message', 'Keranjang dikosongkan');
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50',
        ]);

        $code = trim((string) ($validated['code'] ?? ''));
        if ($code === '') {
            $this->cartService->clearCouponCode();

            return response()->json([
                'success' => true,
                'message' => 'Kupon dihapus.',
            ]);
        }

        $pricing = $this->cartService->getPricingWithFlashSale(
            $this->flashSaleService,
            $this->bundlePricingService
        );
        $result = $this->couponService->applyCoupon(
            $code,
            $pricing['items'],
            $pricing['subtotal'],
            auth('customer')->user(),
            $pricing['flash_sale_promotion_ids'],
            0.0
        );

        if (! $result['valid']) {
            $this->cartService->clearCouponCode();

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Kupon tidak valid.',
            ], 422);
        }

        $this->cartService->setCouponCode($code);

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Kupon berhasil diterapkan.',
            'discount' => $result['discount'],
            'total' => $result['total'],
            'promotion_name' => $result['promotion']?->name,
        ]);
    }

    public function removeCoupon(): JsonResponse
    {
        $this->cartService->clearCouponCode();

        return response()->json([
            'success' => true,
            'message' => 'Kupon dihapus.',
        ]);
    }
}
