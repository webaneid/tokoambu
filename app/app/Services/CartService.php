<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Bundle;
use App\Models\Product;
use App\Services\BundlePricingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Add product to cart
     * @param Product|int $product Product object or ID
     * @param int $quantity Quantity to add
     * @param int|null $variantId Product variant ID (if applicable)
     */
    public function add($product, int $quantity = 1, ?int $variantId = null): CartItem
    {
        if (is_int($product)) {
            $product = Product::findOrFail($product);
        }

        // Get or create cart
        $cart = $this->getCart();

        // Determine price: use variant price if available, otherwise product price
        $price = $product->selling_price; // Default to product price

        if ($variantId) {
            $variant = $product->variants()->findOrFail($variantId);
            $price = $variant->selling_price;

            // Check variant stock (skip for preorder products)
            if (!$product->allow_preorder && $variant->total_stock < $quantity) {
                throw new \Exception('Stok tidak cukup. Tersedia hanya ' . $variant->total_stock . ' unit.');
            }
        } else {
            // Check product stock for simple products (skip for preorder products)
            if (!$product->allow_preorder) {
                $totalStock = $product->inventoryBalances()->sum('qty_on_hand');
                if ($totalStock < $quantity) {
                    throw new \Exception('Stok tidak cukup. Tersedia hanya ' . $totalStock . ' unit.');
                }
            }
        }

        // Check if item already exists (same product + same variant)
        $cartItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($cartItem) {
            // Update quantity
            $cartItem->update([
                'quantity' => $cartItem->quantity + $quantity,
            ]);
        } else {
            // Create new cart item with price snapshot
            $cartItem = $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $price, // Snapshot current price
            ]);
        }

        return $cartItem;
    }

    /**
     * Add bundle to cart
     */
    public function addBundle(Bundle $bundle, int $quantity = 1): CartItem
    {
        $bundle->loadMissing(['items.product', 'items.productVariant']);

        $bundleItems = $bundle->items;
        if ($bundleItems->isEmpty()) {
            throw new \Exception('Bundle tidak memiliki produk.');
        }

        $primaryProductId = $bundleItems->first()->product_id;
        if (! $primaryProductId && $bundleItems->first()->productVariant) {
            $primaryProductId = $bundleItems->first()->productVariant->product_id;
        }
        if (! $primaryProductId) {
            throw new \Exception('Produk bundle tidak valid.');
        }

        $bundlePricing = app(BundlePricingService::class)->calculate($bundle);
        $bundlePrice = (float) $bundlePricing['bundle_price'];

        $cart = $this->getCart();

        $cartItem = $cart->items()
            ->where('bundle_id', $bundle->id)
            ->where('product_id', $primaryProductId)
            ->whereNull('product_variant_id')
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $cartItem->quantity + $quantity,
                'price' => $bundlePrice,
            ]);
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $primaryProductId,
                'product_variant_id' => null,
                'bundle_id' => $bundle->id,
                'quantity' => $quantity,
                'price' => $bundlePrice,
            ]);
        }

        return $cartItem;
    }

    /**
     * Remove cart item by ID
     */
    public function remove(int $cartItemId): bool
    {
        $cart = $this->getCart();

        return (bool) $cart->items()
            ->where('id', $cartItemId)
            ->delete();
    }

    /**
     * Update cart item quantity by cart item ID
     */
    public function update(int $cartItemId, int $quantity): CartItem|bool
    {
        $cart = $this->getCart();

        if ($quantity <= 0) {
            return $this->remove($cartItemId);
        }

        $cartItem = $cart->items()
            ->where('id', $cartItemId)
            ->firstOrFail();

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem;
    }

    /**
     * Clear entire cart
     */
    public function clear(): bool
    {
        $cart = $this->getCart();
        
        return (bool) $cart->items()->delete();
    }

    /**
     * Get all cart items
     */
    public function getItems(): Collection
    {
        return $this->getCart()->items()->with([
            'product.featuredMedia',
            'variant.featuredMedia',
            'bundle.items.product.featuredMedia',
            'bundle.items.productVariant',
            'bundle.promotion',
        ])->get();
    }

    /**
     * Get cart items with flash sale pricing applied.
     *
     * @return array{items: Collection, subtotal: float, total: float, flash_sale_promotion_ids: array<int>}
     */
    public function getPricingWithFlashSale(FlashSaleService $flashSaleService, BundlePricingService $bundlePricingService): array
    {
        $items = $this->getItems();

        if ($items->isEmpty()) {
            return [
                'items' => $items,
                'subtotal' => 0.0,
                'total' => 0.0,
                'flash_sale_promotion_ids' => [],
            ];
        }

        $bundleItems = $items->filter(fn (CartItem $item) => $item->bundle_id);
        $standardItems = $items->filter(fn (CartItem $item) => ! $item->bundle_id);

        $products = $standardItems->map(fn (CartItem $item) => $item->product)
            ->filter()
            ->keyBy('id');
        $variants = $standardItems->map(fn (CartItem $item) => $item->variant)
            ->filter()
            ->keyBy('id');

        $payload = $standardItems->map(fn (CartItem $item) => [
            'cart_item_id' => $item->id,
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->price,
        ])->toArray();

        $pricedItems = $payload
            ? $flashSaleService->applyToItems($payload, $products, $variants)
            : [];
        $priceMap = collect($pricedItems)->keyBy('cart_item_id');

        $subtotal = 0.0;
        $flashSalePromotionIds = [];
        foreach ($standardItems as $item) {
            $pricing = $priceMap->get($item->id);
            $unitPrice = (float) ($pricing['unit_price'] ?? $item->price);
            $originalPrice = $pricing['original_price'] ?? null;
            $flashSalePromotionId = $pricing['flash_sale_promotion_id'] ?? null;

            $item->setAttribute('unit_price', $unitPrice);
            $item->setAttribute('original_price', $originalPrice);
            $item->setAttribute('has_flash_sale', $originalPrice !== null && $originalPrice > $unitPrice);
            $item->setAttribute('flash_sale_promotion_id', $flashSalePromotionId);

            $subtotal += $unitPrice * $item->quantity;

            if ($flashSalePromotionId) {
                $flashSalePromotionIds[] = (int) $flashSalePromotionId;
            }
        }

        foreach ($bundleItems as $item) {
            $bundle = $item->bundle;
            if (! $bundle) {
                continue;
            }

            $bundlePricing = $bundlePricingService->calculate($bundle);
            $bundlePrice = (float) $bundlePricing['bundle_price'];
            $originalTotal = (float) $bundlePricing['original_total'];

            $item->setAttribute('unit_price', $bundlePrice);
            $item->setAttribute('original_price', $originalTotal);
            $item->setAttribute('has_bundle', true);
            $item->setAttribute('bundle_name', $bundle->promotion?->name);
            $item->setAttribute('bundle_items', $bundlePricing['items']);
            $item->setAttribute('bundle_discount_amount', (float) $bundlePricing['discount_amount']);
            $item->setAttribute('bundle_discount_percent', (float) $bundlePricing['discount_percent']);

            $subtotal += $bundlePrice * $item->quantity;

            if ($bundle->promotion_id) {
                $flashSalePromotionIds[] = (int) $bundle->promotion_id;
            }
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'flash_sale_promotion_ids' => array_values(array_unique($flashSalePromotionIds)),
        ];
    }

    public function getCouponCode(): ?string
    {
        return Session::get('cart_coupon_code');
    }

    public function setCouponCode(string $code): void
    {
        Session::put('cart_coupon_code', strtoupper($code));
    }

    public function clearCouponCode(): void
    {
        Session::forget('cart_coupon_code');
    }

    /**
     * Get cart item count
     */
    public function count(): int
    {
        return $this->getCart()->items()->sum('quantity');
    }

    /**
     * Get cart subtotal
     */
    public function getSubtotal(): float
    {
        return (float) $this->getCart()->items()
            ->selectRaw('SUM(price * quantity) as total')
            ->value('total') ?? 0;
    }

    /**
     * Get cart total (including tax/shipping if needed)
     */
    public function getTotal(): float
    {
        return $this->getSubtotal(); // Can add tax, shipping logic here
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->getCart()->items()->count() === 0;
    }

    /**
     * Get or create cart for current user/session
     */
    private function getCart(): Cart
    {
        // If user is authenticated, use customer cart
        if (auth('customer')->check()) {
            return auth('customer')
                ->user()
                ->cart()
                ->firstOrCreate();
        }

        // For guests, use session-based cart
        $sessionId = Session::getId();

        return Cart::firstOrCreate(
            ['session_id' => $sessionId],
            ['customer_id' => null]
        );
    }

    /**
     * Migrate guest cart to authenticated customer
     * Called after customer login
     */
    public function migrateGuestCart(int $customerId, string $sessionId): void
    {
        $guestCart = Cart::where('session_id', $sessionId)
            ->where('customer_id', null)
            ->first();

        if (!$guestCart || $guestCart->items()->count() === 0) {
            return;
        }

        // Get or create customer cart
        $customerCart = Cart::firstOrCreate(
            ['customer_id' => $customerId],
            ['session_id' => null]
        );

        // Merge items
        foreach ($guestCart->items()->get() as $guestItem) {
            $existingItem = $customerCart->items()
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                // Add quantities together
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $guestItem->quantity,
                ]);
            } else {
                // Move item to customer cart
                $guestItem->update(['cart_id' => $customerCart->id]);
            }
        }

        // Delete guest cart
        $guestCart->delete();
    }
}
