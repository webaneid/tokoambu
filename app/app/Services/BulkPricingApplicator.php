<?php

namespace App\Services;

class BulkPricingApplicator
{
    /**
     * Apply bulk pricing to multiple variant combinations.
     *
     * @param array $variants Array of variant data to update
     * @param array $bulkRules Bulk pricing rules from UI
     * @return array Updated variant data with applied pricing
     *
     * Example bulk rules:
     * [
     *     ['attribute' => 'Ukuran', 'value' => 'M', 'cost_price' => 50000, 'selling_price' => 75000],
     *     ['attribute' => 'Warna', 'value' => 'Merah', 'cost_price' => null, 'selling_price' => 80000],
     * ]
     */
    public function applyBulkPricing(array $variants, array $bulkRules): array
    {
        foreach ($variants as &$variant) {
            foreach ($bulkRules as $rule) {
                // Check if this variant matches the rule
                if ($this->variantMatchesRule($variant, $rule)) {
                    // Apply cost_price if specified
                    if (isset($rule['cost_price']) && $rule['cost_price'] !== null) {
                        $variant['cost_price'] = $rule['cost_price'];
                    }

                    // Apply selling_price if specified
                    if (isset($rule['selling_price']) && $rule['selling_price'] !== null) {
                        $variant['selling_price'] = $rule['selling_price'];
                    }

                    // Apply weight if specified
                    if (isset($rule['weight_grams']) && $rule['weight_grams'] !== null) {
                        $variant['weight_grams'] = $rule['weight_grams'];
                    }
                }
            }
        }

        return $variants;
    }

    /**
     * Apply same price to all variants.
     *
     * @param array $variants Array of variant data
     * @param float|null $costPrice Cost price to apply to all
     * @param float|null $sellingPrice Selling price to apply to all
     * @param int|null $weightGrams Weight to apply to all
     * @return array Updated variant data
     */
    public function applyUniformPricing(
        array $variants,
        ?float $costPrice = null,
        ?float $sellingPrice = null,
        ?int $weightGrams = null
    ): array {
        foreach ($variants as &$variant) {
            if ($costPrice !== null) {
                $variant['cost_price'] = $costPrice;
            }

            if ($sellingPrice !== null) {
                $variant['selling_price'] = $sellingPrice;
            }

            if ($weightGrams !== null) {
                $variant['weight_grams'] = $weightGrams;
            }
        }

        return $variants;
    }

    /**
     * Check if variant matches a bulk pricing rule.
     *
     * @param array $variant Variant with variant_attributes
     * @param array $rule Rule with attribute and value
     * @return bool
     */
    protected function variantMatchesRule(array $variant, array $rule): bool
    {
        if (!isset($rule['attribute']) || !isset($rule['value'])) {
            return false;
        }

        $variantAttributes = $variant['variant_attributes'] ?? [];

        // Check if variant has the specified attribute with the specified value
        return isset($variantAttributes[$rule['attribute']])
            && $variantAttributes[$rule['attribute']] === $rule['value'];
    }
}
