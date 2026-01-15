<?php

namespace App\Services;

use App\Models\Bundle;
use App\Models\BundleItem;

class BundlePricingService
{
    /**
     * @return array{items: array<int, array{item: BundleItem, unit_price: float, qty: int, line_total: float}>,
     *               original_total: float,
     *               bundle_price: float,
     *               discount_amount: float,
     *               discount_percent: float}
     */
    public function calculate(Bundle $bundle): array
    {
        $items = [];
        $originalTotal = 0.0;

        foreach ($bundle->items as $item) {
            $unitPrice = (float) ($item->productVariant?->selling_price ?? $item->product?->selling_price ?? 0);
            $lineTotal = $unitPrice * (int) $item->qty;

            $items[] = [
                'item' => $item,
                'unit_price' => $unitPrice,
                'qty' => (int) $item->qty,
                'line_total' => $lineTotal,
            ];

            $originalTotal += $lineTotal;
        }

        $bundlePrice = $this->calculateBundlePrice($bundle, $originalTotal);
        $discountAmount = max(0.0, $originalTotal - $bundlePrice);
        $discountPercent = $originalTotal > 0 ? round(($discountAmount / $originalTotal) * 100, 2) : 0.0;

        return [
            'items' => $items,
            'original_total' => $originalTotal,
            'bundle_price' => $bundlePrice,
            'discount_amount' => $discountAmount,
            'discount_percent' => $discountPercent,
        ];
    }

    public function calculateBundlePrice(Bundle $bundle, float $originalTotal): float
    {
        $bundlePrice = $originalTotal;

        if ($bundle->pricing_mode === 'fixed') {
            $bundlePrice = (float) ($bundle->bundle_price ?? $originalTotal);
        } elseif ($bundle->pricing_mode === 'percent_off') {
            $discountValue = (float) ($bundle->discount_value ?? 0);
            $bundlePrice = $originalTotal * (1 - ($discountValue / 100));
        } elseif ($bundle->pricing_mode === 'amount_off') {
            $discountValue = (float) ($bundle->discount_value ?? 0);
            $bundlePrice = $originalTotal - $discountValue;
        }

        if ($bundle->must_be_cheaper) {
            $bundlePrice = min($bundlePrice, $originalTotal);
        }

        return max(0.0, round($bundlePrice, 2));
    }
}
