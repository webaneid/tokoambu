<?php

namespace App\Services;

class VariantGenerator
{
    /**
     * Generate all possible variant combinations from variant groups.
     *
     * @param array $variantGroups Example: [['name' => 'Ukuran', 'options' => ['M', 'L']], ['name' => 'Warna', 'options' => ['Merah', 'Biru']]]
     * @return array Array of combinations, each as associative array: [['Ukuran' => 'M', 'Warna' => 'Merah'], ...]
     */
    public function generateCombinations(array $variantGroups): array
    {
        if (empty($variantGroups)) {
            return [];
        }

        // Start with first group
        $firstGroup = array_shift($variantGroups);
        $combinations = [];

        foreach ($firstGroup['options'] as $option) {
            $combinations[] = [$firstGroup['name'] => $option];
        }

        // Combine with remaining groups (Cartesian product)
        foreach ($variantGroups as $group) {
            $newCombinations = [];

            foreach ($combinations as $existingCombination) {
                foreach ($group['options'] as $option) {
                    $newCombination = $existingCombination;
                    $newCombination[$group['name']] = $option;
                    $newCombinations[] = $newCombination;
                }
            }

            $combinations = $newCombinations;
        }

        return $combinations;
    }

    /**
     * Generate SKU for a variant based on parent product SKU.
     *
     * @param string $parentSku Parent product SKU (e.g., 'KAOS-001')
     * @param int $variantIndex Index of variant (0-based)
     * @return string Generated SKU (e.g., 'KAOS-001-1')
     */
    public function generateVariantSku(string $parentSku, int $variantIndex): string
    {
        return $parentSku . '-' . ($variantIndex + 1);
    }

    /**
     * Count total number of combinations that will be generated.
     *
     * @param array $variantGroups
     * @return int Total combinations
     */
    public function countCombinations(array $variantGroups): int
    {
        if (empty($variantGroups)) {
            return 0;
        }

        $total = 1;

        foreach ($variantGroups as $group) {
            $total *= count($group['options']);
        }

        return $total;
    }
}
