<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Province;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\BundlePricingService;
use App\Services\CouponService;
use App\Services\FlashSaleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CouponService $couponService,
        protected BundlePricingService $bundlePricingService,
        protected FlashSaleService $flashSaleService
    ) {}

    /**
     * Display checkout form
     */
    public function index(): View
    {
        // Get cart items with flash sale pricing
        $pricing = $this->cartService->getPricingWithFlashSale(
            $this->flashSaleService,
            $this->bundlePricingService
        );
        $items = $pricing['items'];

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('message', 'Keranjang Anda kosong');
        }

        $subtotal = $pricing['subtotal'];
        $shipping = 0; // Calculate based on shipping location
        $couponCode = $this->cartService->getCouponCode();
        $couponResult = $this->couponService->applyCoupon(
            $couponCode,
            $items,
            $subtotal,
            auth('customer')->user(),
            $pricing['flash_sale_promotion_ids'],
            $shipping
        );
        if ($couponCode && ! $couponResult['valid']) {
            $this->cartService->clearCouponCode();
            $couponCode = null;
        }

        $total = $couponResult['total'];

        // Get customer data if authenticated
        $customer = auth('customer')->user();

        // Load customer relations for address pre-population
        if ($customer) {
            $customer->load(['province', 'city', 'district']);
        }

        // Get provinces for autocomplete
        $provinces = Province::orderBy('name')->get();

        // Get origin for shipping calculation
        $origin = [
            'province_id' => Setting::get('origin_province_id'),
            'province_name' => Setting::get('origin_province_name'),
            'city_id' => Setting::get('origin_city_id'),
            'city_name' => Setting::get('origin_city_name'),
            'district_id' => Setting::get('origin_district_id'),
            'district_name' => Setting::get('origin_district_name'),
            'postal_code' => Setting::get('origin_postal_code'),
        ];

        // Get active couriers from settings
        $activeCouriers = json_decode(Setting::get('active_couriers', '[]'), true) ?: [];
        $couriers = config('rajaongkir.couriers', []);

        return view('storefront.checkout.index', [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'customer' => $customer,
            'provinces' => $provinces,
            'origin' => $origin,
            'couriers' => $couriers,
            'activeCouriers' => $activeCouriers,
            'couponCode' => $couponCode,
            'couponDiscount' => $couponResult['discount'],
            'couponMessage' => $couponResult['message'],
            'couponPromotion' => $couponResult['promotion'],
        ]);
    }

    /**
     * Create order from checkout form
     */
    public function store(CreateOrderRequest $request): RedirectResponse
    {
        // Get cart items with product relationships loaded
        $pricing = $this->cartService->getPricingWithFlashSale(
            $this->flashSaleService,
            $this->bundlePricingService
        );
        $cartItems = $pricing['items'];

        // Eager load product and preorder periods relationships
        $cartItems->load('product.preorderPeriods');

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang Anda kosong');
        }

        try {
            DB::beginTransaction();

            // Get logged-in customer (required for storefront orders)
            $customer = auth('customer')->user();

            if (! $customer) {
                return redirect()->route('customer.login')
                    ->with('error', 'Anda harus login terlebih dahulu untuk checkout.');
            }

            // Calculate total
            $subtotal = $pricing['subtotal'];
            $shippingCost = $request->shipping_cost ?? 0;
            $couponCode = $this->cartService->getCouponCode();
            $couponResult = $this->couponService->applyCoupon(
                $couponCode,
                $cartItems,
                $subtotal,
                $customer,
                $pricing['flash_sale_promotion_ids'],
                (float) $shippingCost
            );

            if ($couponCode && ! $couponResult['valid']) {
                $this->cartService->clearCouponCode();
                return redirect()->route('checkout.index')
                    ->with('error', $couponResult['message'] ?? 'Kupon tidak valid.');
            }

            $discountAmount = $couponResult['discount'] ?? 0.0;
            $totalAmount = $subtotal + $shippingCost - $discountAmount;

            // Update customer address if provided (optional - for future auto-fill)
            if ($request->shipping_province_id && $request->shipping_city_id && $request->shipping_district_id) {
                $customer->update([
                    'address' => $request->shipping_address,
                    'province_id' => $request->shipping_province_id,
                    'city_id' => $request->shipping_city_id,
                    'district_id' => $request->shipping_district_id,
                    'postal_code' => $request->shipping_postal_code,
                ]);
            }

            // Detect if order contains preorder products
            $hasPreorderProduct = false;
            $activePreorderPeriod = null;
            foreach ($cartItems as $cartItem) {
                // Load product relationship if not loaded
                if (!$cartItem->relationLoaded('product')) {
                    $cartItem->load('product.preorderPeriods');
                }

                $product = $cartItem->product;

                if ($product && $product->allow_preorder) {
                    // Check if product has active preorder period
                    $activePreorderPeriod = $product->preorderPeriods()
                        ->where('status', 'active')
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();

                    if ($activePreorderPeriod) {
                        $hasPreorderProduct = true;
                        break;
                    }
                }
            }

            // Determine order type and initial status
            $orderType = $hasPreorderProduct ? 'preorder' : 'order';
            $dpRequired = Setting::isPreorderDpRequired();
            $orderStatus = ($hasPreorderProduct && $dpRequired) ? 'waiting_dp' : 'waiting_payment';

            // Create order
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_number' => $this->generateOrderNumber(),
                'type' => $orderType,
                'status' => $orderStatus,
                'source' => 'storefront',
                'preorder_period_id' => $activePreorderPeriod?->id,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'shipping_province_id' => $request->shipping_province_id,
                'shipping_city_id' => $request->shipping_city_id,
                'shipping_district_id' => $request->shipping_district_id,
                'shipping_postal_code' => $request->shipping_postal_code,
                'shipping_address' => $request->shipping_address,
                'shipping_courier' => $request->shipping_courier,
                'shipping_service' => $request->shipping_service,
                'shipping_etd' => $request->shipping_etd,
                'notes' => $request->notes,
                'ip_address' => $request->ip(),
            ]);

            // Create order items from cart
            foreach ($cartItems as $cartItem) {
                if ($cartItem->bundle_id && $cartItem->bundle) {
                    $bundlePricing = $this->bundlePricingService->calculate($cartItem->bundle);
                    $originalTotal = (float) $bundlePricing['original_total'];
                    $bundleDiscount = (float) $bundlePricing['discount_amount'];

                    foreach ($bundlePricing['items'] as $bundleItemData) {
                        $bundleItem = $bundleItemData['item'];
                        $baseLineTotal = (float) $bundleItemData['line_total'];
                        $share = $originalTotal > 0 ? ($baseLineTotal / $originalTotal) : 0.0;
                        $lineDiscount = $bundleDiscount * $share;
                        $discountedLineTotal = max(0.0, $baseLineTotal - $lineDiscount);

                        $itemQty = (int) $bundleItemData['qty'] * (int) $cartItem->quantity;
                        $unitPrice = $itemQty > 0
                            ? round(($discountedLineTotal * $cartItem->quantity) / $itemQty, 2)
                            : 0.0;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $bundleItem->product_id ?? $bundleItem->productVariant?->product_id,
                            'product_variant_id' => $bundleItem->product_variant_id,
                            'price' => $bundleItemData['unit_price'],
                            'unit_price' => $unitPrice,
                            'quantity' => $itemQty,
                            'subtotal' => $unitPrice * $itemQty,
                        ]);
                    }

                    continue;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id ?? null,
                    'price' => $cartItem->original_price ?? null,
                    'unit_price' => $cartItem->unit_price ?? $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => ($cartItem->unit_price ?? $cartItem->price) * $cartItem->quantity,
                ]);
            }

            if (! empty($couponResult['promotion'])) {
                \App\Models\PromotionUsage::create([
                    'promotion_id' => $couponResult['promotion']->id,
                    'order_id' => $order->id,
                    'user_id' => $customer->id,
                    'coupon_code' => $couponCode ? strtoupper($couponCode) : null,
                    'discount_amount' => $discountAmount,
                    'applied_at' => now(),
                ]);
            }

            // Clear cart
            $this->cartService->clear();
            $this->cartService->clearCouponCode();

            DB::commit();

            return redirect()->route('order.confirmation', $order->id)
                ->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();

            // Log error for debugging
            \Log::error('Checkout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
