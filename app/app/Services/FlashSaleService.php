<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class FlashSaleService
{
    /**
     * Apply flash sale pricing to order items.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, ProductVariant>  $variants
     * @return array<int, array<string, mixed>>
     */
    public function applyToItems(array $items, Collection $products, Collection $variants): array
    {
        $promotions = $this->getActiveFlashSales();

        if ($promotions->isEmpty()) {
            return $items;
        }

        foreach ($items as $index => $item) {
            $product = $products->get($item['product_id']);
            $variant = ! empty($item['product_variant_id'])
                ? $variants->get($item['product_variant_id'])
                : null;

            if (! $product) {
                continue;
            }

            $promotion = $this->findMatchingPromotion($promotions, $product, $variant, (int) $item['quantity']);
            if (! $promotion) {
                continue;
            }

            $benefit = $promotion->benefits->firstWhere('apply_scope', 'item')
                ?? $promotion->benefits->first();

            if (! $benefit || $benefit->benefit_type === 'free_shipping' || $benefit->apply_scope !== 'item') {
                continue;
            }

            $basePrice = (float) $item['unit_price'];
            $discountedPrice = $this->applyBenefit($benefit->benefit_type, $benefit->value, $benefit->max_discount, $basePrice);

            if ($discountedPrice < $basePrice) {
                $items[$index]['original_price'] = $basePrice;
                $items[$index]['unit_price'] = $discountedPrice;
                $items[$index]['flash_sale_promotion_id'] = $promotion->id;
            }
        }

        return $items;
    }

    private function getActiveFlashSales(): Collection
    {
        $now = now();

        return Promotion::query()
            ->where('type', 'flash_sale')
            ->where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', $now);
            })
            ->with(['targets', 'benefits'])
            ->orderByDesc('priority')
            ->get();
    }

    private function findMatchingPromotion(Collection $promotions, Product $product, ?ProductVariant $variant, int $qty): ?Promotion
    {
        $productId = $product->id;
        $variantId = $variant?->id;
        $stock = $variant ? (float) $variant->total_stock : (float) $product->qty_on_hand;

        foreach ($promotions as $promotion) {
            if (! $this->matchesTargets($promotion, $productId, $variantId)) {
                continue;
            }

            if (! $this->passesRules($promotion->rules ?? [], $qty, $stock)) {
                continue;
            }

            return $promotion;
        }

        return null;
    }

    private function matchesTargets(Promotion $promotion, int $productId, ?int $variantId): bool
    {
        if ($promotion->targets->isEmpty()) {
            return false;
        }

        $matched = false;

        foreach ($promotion->targets as $target) {
            if ($target->target_type === 'variant' && $variantId && (int) $target->target_id === $variantId) {
                if (! $target->include) {
                    return false;
                }
                $matched = true;
            }

            if ($target->target_type === 'product' && (int) $target->target_id === $productId) {
                if (! $target->include) {
                    return false;
                }
                $matched = true;
            }
        }

        return $matched;
    }

    private function passesRules(array $rules, int $qty, float $stock): bool
    {
        $minQty = $rules['min_qty'] ?? null;
        $maxQty = $rules['max_qty'] ?? null;
        $minStock = $rules['min_stock_threshold'] ?? null;

        if ($minQty !== null && $qty < (int) $minQty) {
            return false;
        }

        if ($maxQty !== null && $qty > (int) $maxQty) {
            return false;
        }

        if ($minStock !== null && $stock < (float) $minStock) {
            return false;
        }

        return true;
    }

    private function applyBenefit(string $type, float $value, ?float $maxDiscount, float $basePrice): float
    {
        $discount = 0.0;

        if ($type === 'percent_off') {
            $discount = $basePrice * ($value / 100);
        } elseif ($type === 'amount_off') {
            $discount = $value;
        } elseif ($type === 'fixed_price') {
            return max(0.0, min($basePrice, $value));
        } else {
            return $basePrice;
        }

        if ($maxDiscount !== null && $discount > $maxDiscount) {
            $discount = $maxDiscount;
        }

        return max(0.0, $basePrice - $discount);
    }
}
