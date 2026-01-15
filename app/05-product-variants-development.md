# Product Variants System - Development Guide

## Overview

Product Variants System memungkinkan produk untuk memiliki variasi (seperti Ukuran, Warna, Material) dengan harga dan stok yang berbeda per kombinasi variasi. Sistem ini harus backward compatible dengan produk existing yang tidak memiliki variants.

---

## 1. Core Concepts

### 1.1 Product Types

**Simple Product (Tanpa Variant)**
- Produk dengan harga dan stok tunggal
- Contoh: Buku, Notebook, Alat Tulis
- Tetap menggunakan field `cost_price`, `selling_price` di table `products`

**Variable Product (Dengan Variants)**
- Produk dengan multiple kombinasi variasi
- Contoh: Kaos (Ukuran × Warna), Sepatu (Ukuran × Warna × Model)
- Harga dan stok disimpan di table `product_variants`

### 1.2 Variant Attributes

- **Flexible per Product** - tidak ada master list
- **Free-form text** - user input nama attribute dan values
- **Multiple Attributes** - bisa 1 atau lebih (Warna, Ukuran, Material, dll)
- **Stored as JSON** - `{"Ukuran": "M", "Warna": "Merah"}`

### 1.3 SKU Generation

**Format:** `PARENT-SKU-{sequence}`

Example:
- Parent Product SKU: `KAOS-001`
- Variant 1 (Merah-M): `KAOS-001-1`
- Variant 2 (Merah-L): `KAOS-001-2`
- Variant 3 (Biru-M): `KAOS-001-3`

**Auto-increment:** Sequential number based on existing variants count

### 1.4 Dynamic Variants

- Product dapat **diubah** dari simple → variable (dan sebaliknya)
- Variants dapat **ditambahkan** kapan saja (even after sales)
- Variants dapat **dinonaktifkan** (`is_active = false`) tapi **tidak dihapus** jika sudah ada transaksi
- **Soft constraint:** Cannot delete variant yang sudah ada di `order_items`, `purchase_items`, atau `stock_movements`

---

## 2. Database Schema

### 2.1 Modify Existing Table: `products`

**Add Columns:**
```sql
ALTER TABLE products ADD COLUMN has_variants BOOLEAN DEFAULT false AFTER is_active;
ALTER TABLE products ADD COLUMN variant_groups JSON NULL AFTER has_variants
  COMMENT 'Variant group definitions, e.g. [{"name":"Ukuran","options":["M","L","XL"]},{"name":"Warna","options":["Merah","Biru"]}]';
```

**Business Rules:**
- `has_variants = false` → simple product
  - `cost_price`, `selling_price` REQUIRED
  - `weight_grams` used as default
  - `variant_groups` must be NULL

- `has_variants = true` → variable product
  - `cost_price`, `selling_price` can be NULL (pricing di variant level)
  - `weight_grams` used as default, can be overridden per variant
  - `variant_groups` REQUIRED - contains array of variant group definitions

**Variant Groups Structure:**
```json
[
  {
    "name": "Ukuran",
    "options": ["M", "L", "XL", "XXL"]
  },
  {
    "name": "Warna",
    "options": ["Merah", "Biru", "Hijau"]
  }
]
```

**Notes:**
- Can have 1 or more variant groups (minimum 1 if has_variants = true)
- System will generate Cartesian product of all options across groups
- Example: 4 sizes × 3 colors = 12 variant combinations

### 2.2 New Table: `product_variants`

```sql
CREATE TABLE product_variants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(255) NOT NULL UNIQUE,
    variant_attributes JSON NOT NULL COMMENT 'Attributes as key-value pairs, e.g. {"Warna": "Merah", "Ukuran": "M"}',
    cost_price DECIMAL(15,2) NOT NULL,
    selling_price DECIMAL(15,2) NOT NULL,
    weight_grams INT UNSIGNED NULL COMMENT 'Null = inherit from parent product',
    is_active BOOLEAN DEFAULT true,
    featured_media_id BIGINT UNSIGNED NULL COMMENT 'Variant-specific image (optional)',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    INDEX idx_product_id (product_id),
    INDEX idx_sku (sku),
    INDEX idx_is_active (is_active),

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (featured_media_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Constraints:**
- `sku` must be UNIQUE globally
- `variant_attributes` must be valid JSON
- Cannot delete variant if referenced by transactions

### 2.3 Modify Table: `inventory_balances`

**Add Column:**
```sql
ALTER TABLE inventory_balances ADD COLUMN product_variant_id BIGINT UNSIGNED NULL AFTER product_id;
ALTER TABLE inventory_balances ADD INDEX idx_variant (product_variant_id);
ALTER TABLE inventory_balances ADD FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE;
```

**Composite Unique Key:**
```sql
-- Remove old unique key
ALTER TABLE inventory_balances DROP INDEX unique_product_location;

-- Add new composite unique key
ALTER TABLE inventory_balances ADD UNIQUE KEY unique_product_variant_location (product_id, product_variant_id, location_id);
```

**Business Rules:**
- If `product_variant_id` IS NULL → tracking simple product
- If `product_variant_id` IS NOT NULL → tracking specific variant
- Cannot have both NULL `product_variant_id` for same `product_id` + `location_id` if product has variants

### 2.4 Modify Table: `order_items`

**Add Column:**
```sql
ALTER TABLE order_items ADD COLUMN product_variant_id BIGINT UNSIGNED NULL AFTER product_id;
ALTER TABLE order_items ADD INDEX idx_variant (product_variant_id);
ALTER TABLE order_items ADD FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE RESTRICT;
```

**Business Rules:**
- If product `has_variants = true` → `product_variant_id` REQUIRED
- If product `has_variants = false` → `product_variant_id` must be NULL
- Price at order time captured in `price` column (variant price can change later)

### 2.5 Modify Table: `purchase_items`

**Add Column:**
```sql
ALTER TABLE purchase_items ADD COLUMN product_variant_id BIGINT UNSIGNED NULL AFTER product_id;
ALTER TABLE purchase_items ADD INDEX idx_variant (product_variant_id);
ALTER TABLE purchase_items ADD FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE RESTRICT;
```

**Business Rules:**
- Same as `order_items`
- Supplier delivers specific variant

### 2.6 Modify Table: `stock_movements`

**Add Column:**
```sql
ALTER TABLE stock_movements ADD COLUMN product_variant_id BIGINT UNSIGNED NULL AFTER product_id;
ALTER TABLE stock_movements ADD INDEX idx_variant (product_variant_id);
ALTER TABLE stock_movements ADD FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE RESTRICT;
```

**Business Rules:**
- All stock movements for variants must reference `product_variant_id`
- Audit trail preserved even if variant deactivated

---

## 3. Model Layer

### 3.1 Product Model Updates

**File:** `app/Models/Product.php`

**Add Relations:**
```php
public function variants()
{
    return $this->hasMany(ProductVariant::class)->orderBy('sku');
}

public function activeVariants()
{
    return $this->hasMany(ProductVariant::class)->where('is_active', true)->orderBy('sku');
}
```

**Add Attributes:**
```php
protected $fillable = [
    // ... existing fields
    'has_variants',
    'variant_groups',
];

protected $casts = [
    // ... existing casts
    'has_variants' => 'boolean',
    'variant_groups' => 'array',
];
```

**Add Methods:**
```php
/**
 * Get effective weight (considering variant override or parent default)
 */
public function getEffectiveWeight(?ProductVariant $variant = null): int
{
    if ($variant && $variant->weight_grams !== null) {
        return $variant->weight_grams;
    }
    return $this->weight_grams ?? 0;
}

/**
 * Check if product can be converted to variable product
 */
public function canHaveVariants(): bool
{
    // Cannot add variants if already has inventory or orders as simple product
    $hasSimpleInventory = $this->inventoryBalances()->whereNull('product_variant_id')->exists();
    $hasSimpleOrders = OrderItem::where('product_id', $this->id)->whereNull('product_variant_id')->exists();

    return !$hasSimpleInventory && !$hasSimpleOrders;
}

/**
 * Get total stock across all variants (or simple product)
 */
public function getTotalStock(): float
{
    if ($this->has_variants) {
        return $this->inventoryBalances()
            ->whereNotNull('product_variant_id')
            ->sum('qty_on_hand');
    }
    return $this->qty_on_hand ?? 0;
}
```

### 3.2 New Model: ProductVariant

**File:** `app/Models/ProductVariant.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'variant_attributes',
        'cost_price',
        'selling_price',
        'weight_grams',
        'is_active',
        'featured_media_id',
    ];

    protected $casts = [
        'variant_attributes' => 'array',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'weight_grams' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relations
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function inventoryBalances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Accessors & Mutators
     */
    public function getDisplayNameAttribute(): string
    {
        $attrs = [];
        foreach ($this->variant_attributes as $key => $value) {
            $attrs[] = "$key: $value";
        }
        return $this->product->name . ' (' . implode(', ', $attrs) . ')';
    }

    public function getEffectiveWeightAttribute(): int
    {
        return $this->weight_grams ?? $this->product->weight_grams ?? 0;
    }

    /**
     * Business Logic
     */
    public function profit(): float
    {
        return $this->selling_price - $this->cost_price;
    }

    public function profitMargin(): float
    {
        if ($this->selling_price == 0) return 0;
        return ($this->profit() / $this->selling_price) * 100;
    }

    /**
     * Get total stock across all locations
     */
    public function getTotalStock(): float
    {
        return $this->inventoryBalances()->sum('qty_on_hand');
    }

    /**
     * Check if variant can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Cannot delete if has any transactions
        return !$this->orderItems()->exists()
            && !$this->purchaseItems()->exists()
            && !$this->stockMovements()->exists();
    }

    /**
     * Generate next SKU for product
     */
    public static function generateSku(Product $product): string
    {
        $parentSku = $product->sku;
        $maxVariant = static::where('product_id', $product->id)
            ->where('sku', 'like', "$parentSku-%")
            ->selectRaw("CAST(SUBSTRING_INDEX(sku, '-', -1) AS UNSIGNED) as seq")
            ->orderByDesc('seq')
            ->value('seq');

        $nextSeq = ($maxVariant ?? 0) + 1;
        return "$parentSku-$nextSeq";
    }
}
```

### 3.3 Update Existing Models

**InventoryBalance Model:**
```php
// Add relation
public function productVariant(): BelongsTo
{
    return $this->belongsTo(ProductVariant::class);
}

// Update accessor
public function getItemNameAttribute(): string
{
    if ($this->product_variant_id) {
        return $this->productVariant->display_name ?? 'Unknown Variant';
    }
    return $this->product->name ?? 'Unknown Product';
}
```

**OrderItem Model:**
```php
// Add relation
public function productVariant(): BelongsTo
{
    return $this->belongsTo(ProductVariant::class);
}

// Update accessor
public function getItemNameAttribute(): string
{
    if ($this->product_variant_id) {
        return $this->productVariant->display_name ?? $this->product->name;
    }
    return $this->product->name;
}
```

**PurchaseItem Model:**
```php
// Add relation (same as OrderItem)
```

**StockMovement Model:**
```php
// Add relation (same as OrderItem)
```

---

## 4. Service Layer Updates

### 4.1 InventoryService Updates

**File:** `app/Domain/Inventory/Services/InventoryService.php`

**Update Method Signatures:**

All methods that currently accept `Product $product` should also accept optional `ProductVariant $variant = null`:

```php
public function adjustStock(
    Product $product,
    Location $location,
    float $quantity,
    string $reason,
    ?ProductVariant $variant = null,
    ?string $referenceType = null,
    ?int $referenceId = null
): StockMovement;

public function transferStock(
    Product $product,
    Location $fromLocation,
    Location $toLocation,
    float $quantity,
    ?ProductVariant $variant = null,
    ?string $notes = null
): array;

public function reserveStock(
    Product $product,
    Location $location,
    float $quantity,
    ?ProductVariant $variant = null,
    string $referenceType,
    int $referenceId
): bool;
```

**Update Implementation:**

```php
protected function getOrCreateBalance(Product $product, Location $location, ?ProductVariant $variant = null): InventoryBalance
{
    return InventoryBalance::firstOrCreate([
        'product_id' => $product->id,
        'product_variant_id' => $variant?->id,
        'location_id' => $location->id,
    ], [
        'qty_on_hand' => 0,
        'qty_reserved' => 0,
    ]);
}

protected function createMovement(
    Product $product,
    Location $location,
    float $quantity,
    string $type,
    ?ProductVariant $variant = null,
    ?string $referenceType = null,
    ?int $referenceId = null,
    ?string $notes = null
): StockMovement {
    return StockMovement::create([
        'product_id' => $product->id,
        'product_variant_id' => $variant?->id,
        'location_id' => $location->id,
        'quantity' => $quantity,
        'type' => $type,
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
        'notes' => $notes,
    ]);
}
```

### 4.2 Event Handler Updates

**PurchaseReceived Event Handler:**

```php
// In Domain/Inventory/Listeners/HandlePurchaseReceived.php

public function handle(PurchaseReceived $event): void
{
    $purchase = $event->purchase;
    $purchase->load('items.product', 'items.productVariant', 'warehouse.locations');

    foreach ($purchase->items as $item) {
        $location = $purchase->warehouse->locations->first();

        $this->inventoryService->adjustStock(
            product: $item->product,
            location: $location,
            quantity: $item->quantity,
            reason: 'Receive from purchase',
            variant: $item->productVariant, // <-- VARIANT SUPPORT
            referenceType: Purchase::class,
            referenceId: $purchase->id
        );
    }
}
```

**OrderPackedOrShipped Event Handler:**

```php
// In Domain/Inventory/Listeners/HandleOrderPackedOrShipped.php

public function handle(OrderPackedOrShipped $event): void
{
    $order = $event->order;
    $order->load('items.product', 'items.productVariant');

    foreach ($order->items as $item) {
        if (!$item->is_preorder) {
            $location = $this->determineLocation($order);

            $this->inventoryService->adjustStock(
                product: $item->product,
                location: $location,
                quantity: -$item->quantity, // negative = reduce
                reason: 'Ship to customer',
                variant: $item->productVariant, // <-- VARIANT SUPPORT
                referenceType: Order::class,
                referenceId: $order->id
            );
        }
    }
}
```

---

## 5. Variant Generation Logic

### 5.1 Cartesian Product Generator

**Purpose:** Generate all possible combinations from variant groups

**Helper Class:** `app/Services/VariantGenerator.php`

```php
<?php

namespace App\Services;

class VariantGenerator
{
    /**
     * Generate Cartesian product of variant groups
     *
     * @param array $variantGroups [{"name": "Ukuran", "options": ["M", "L"]}, ...]
     * @return array Array of attribute combinations
     */
    public static function generateCombinations(array $variantGroups): array
    {
        if (empty($variantGroups)) {
            return [];
        }

        // Start with first group
        $combinations = [];
        foreach ($variantGroups[0]['options'] as $option) {
            $combinations[] = [$variantGroups[0]['name'] => $option];
        }

        // Cross-product with remaining groups
        for ($i = 1; $i < count($variantGroups); $i++) {
            $group = $variantGroups[$i];
            $newCombinations = [];

            foreach ($combinations as $existing) {
                foreach ($group['options'] as $option) {
                    $newCombinations[] = array_merge(
                        $existing,
                        [$group['name'] => $option]
                    );
                }
            }

            $combinations = $newCombinations;
        }

        return $combinations;
    }

    /**
     * Example Usage:
     *
     * $groups = [
     *     ['name' => 'Ukuran', 'options' => ['M', 'L', 'XL']],
     *     ['name' => 'Warna', 'options' => ['Merah', 'Biru']]
     * ];
     *
     * $result = VariantGenerator::generateCombinations($groups);
     *
     * // Returns:
     * [
     *     ['Ukuran' => 'M', 'Warna' => 'Merah'],
     *     ['Ukuran' => 'M', 'Warna' => 'Biru'],
     *     ['Ukuran' => 'L', 'Warna' => 'Merah'],
     *     ['Ukuran' => 'L', 'Warna' => 'Biru'],
     *     ['Ukuran' => 'XL', 'Warna' => 'Merah'],
     *     ['Ukuran' => 'XL', 'Warna' => 'Biru'],
     * ]
     */
}
```

### 5.2 Bulk Pricing Application Logic

**Helper Class:** `app/Services/BulkPricingApplicator.php`

```php
<?php

namespace App\Services;

class BulkPricingApplicator
{
    /**
     * Apply bulk pricing rules to variant combinations
     *
     * @param array $combinations Generated from VariantGenerator
     * @param array $pricingRules From UI input
     * @return array Combinations with prices applied
     */
    public static function applyPricing(array $combinations, array $pricingRules): array
    {
        $result = [];

        foreach ($combinations as $combination) {
            $pricing = self::findMatchingRule($combination, $pricingRules);

            $result[] = array_merge($combination, [
                'cost_price' => $pricing['cost_price'] ?? 0,
                'selling_price' => $pricing['selling_price'] ?? 0,
            ]);
        }

        return $result;
    }

    /**
     * Find pricing rule that matches the combination
     * Priority: specific match > partial match > default
     */
    private static function findMatchingRule(array $combination, array $pricingRules): ?array
    {
        // Try exact match first
        foreach ($pricingRules as $rule) {
            if (self::isExactMatch($combination, $rule['attributes'])) {
                return $rule;
            }
        }

        // Try partial match (e.g., Ukuran = M, Warna = any)
        foreach ($pricingRules as $rule) {
            if (self::isPartialMatch($combination, $rule['attributes'])) {
                return $rule;
            }
        }

        return null;
    }

    private static function isExactMatch(array $combination, array $ruleAttributes): bool
    {
        return $combination === $ruleAttributes;
    }

    private static function isPartialMatch(array $combination, array $ruleAttributes): bool
    {
        foreach ($ruleAttributes as $key => $value) {
            if ($value === '*') continue; // Wildcard (matches any)
            if (!isset($combination[$key]) || $combination[$key] !== $value) {
                return false;
            }
        }
        return true;
    }
}
```

**Example Pricing Rules from UI:**

```json
[
  {
    "attributes": {"Ukuran": "M"},
    "cost_price": 50000,
    "selling_price": 100000
  },
  {
    "attributes": {"Ukuran": "L"},
    "cost_price": 55000,
    "selling_price": 110000
  }
]
```

This will apply M pricing to all M combinations (M+Merah, M+Biru, M+Hijau).

---

## 6. Controller Layer

### 6.1 New Controller: ProductVariantController

**File:** `app/Http/Controllers/ProductVariantController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    /**
     * Store multiple variants for a product
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'variants' => 'required|array|min:1',
            'variants.*.attributes' => 'required|array',
            'variants.*.cost_price' => 'required|numeric|min:0',
            'variants.*.selling_price' => 'required|numeric|min:0',
            'variants.*.weight_grams' => 'nullable|integer|min:0',
            'variants.*.featured_media_id' => 'nullable|exists:media,id',
        ]);

        DB::transaction(function () use ($product, $validated) {
            // Enable variants for product
            $product->update(['has_variants' => true]);

            foreach ($validated['variants'] as $variantData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => ProductVariant::generateSku($product),
                    'variant_attributes' => $variantData['attributes'],
                    'cost_price' => $variantData['cost_price'],
                    'selling_price' => $variantData['selling_price'],
                    'weight_grams' => $variantData['weight_grams'] ?? null,
                    'featured_media_id' => $variantData['featured_media_id'] ?? null,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Variants created successfully');
    }

    /**
     * Update a single variant
     */
    public function update(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'attributes' => 'required|array',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'weight_grams' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'featured_media_id' => 'nullable|exists:media,id',
        ]);

        $variant->update([
            'variant_attributes' => $validated['attributes'],
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'weight_grams' => $validated['weight_grams'] ?? null,
            'is_active' => $validated['is_active'] ?? $variant->is_active,
            'featured_media_id' => $validated['featured_media_id'] ?? $variant->featured_media_id,
        ]);

        return redirect()
            ->route('products.show', $variant->product)
            ->with('success', 'Variant updated successfully');
    }

    /**
     * Delete a variant (only if no transactions)
     */
    public function destroy(ProductVariant $variant)
    {
        if (!$variant->canBeDeleted()) {
            return back()->with('error', 'Cannot delete variant with existing transactions');
        }

        $product = $variant->product;
        $variant->delete();

        // If no more variants, revert to simple product
        if ($product->variants()->count() === 0) {
            $product->update(['has_variants' => false]);
        }

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Variant deleted successfully');
    }
}
```

### 6.2 Update ProductController

**Add method to manage variants UI:**

```php
/**
 * Show variant management interface
 */
public function variants(Product $product)
{
    $product->load(['variants.featuredMedia', 'variants.inventoryBalances.location.warehouse']);

    return view('products.variants', compact('product'));
}

/**
 * Store product with variants
 */
public function storeWithVariants(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'sku' => 'required|string|unique:products',
        // ... other product fields
        'has_variants' => 'boolean',
        'variant_groups' => 'required_if:has_variants,true|array',
        'pricing_rules' => 'required_if:has_variants,true|array',
    ]);

    DB::transaction(function () use ($validated) {
        // Create product
        $product = Product::create([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'has_variants' => $validated['has_variants'] ?? false,
            'variant_groups' => $validated['variant_groups'] ?? null,
            // ...
        ]);

        if ($product->has_variants) {
            // Generate all combinations
            $combinations = VariantGenerator::generateCombinations($product->variant_groups);

            // Apply pricing rules
            $variantsData = BulkPricingApplicator::applyPricing(
                $combinations,
                $validated['pricing_rules']
            );

            // Create variants
            foreach ($variantsData as $variantData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => ProductVariant::generateSku($product),
                    'variant_attributes' => [
                        'Ukuran' => $variantData['Ukuran'],
                        'Warna' => $variantData['Warna'],
                        // Dynamic based on variant_groups
                    ],
                    'cost_price' => $variantData['cost_price'],
                    'selling_price' => $variantData['selling_price'],
                ]);
            }
        }

        return redirect()->route('products.show', $product);
    });
}
```

---

## 7. Routes

**File:** `routes/web.php`

```php
// Product Variants Management
Route::middleware(['auth'])->group(function () {
    Route::get('/products/{product}/variants', [ProductController::class, 'variants'])
        ->name('products.variants');

    Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])
        ->name('product-variants.store');

    Route::put('/product-variants/{variant}', [ProductVariantController::class, 'update'])
        ->name('product-variants.update');

    Route::delete('/product-variants/{variant}', [ProductVariantController::class, 'destroy'])
        ->name('product-variants.destroy');
});
```

---

## 8. Migration Files

### 8.1 Add has_variants and variant_groups to products

**File:** `database/migrations/YYYY_MM_DD_add_variants_support_to_products.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_variants')->default(false)->after('is_active');
            $table->json('variant_groups')->nullable()->after('has_variants')
                ->comment('Variant group definitions');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_variants', 'variant_groups']);
        });
    }
};
```

### 8.2 Create product_variants table

**File:** `database/migrations/YYYY_MM_DD_create_product_variants_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->json('variant_attributes')->comment('Attributes as key-value pairs');
            $table->decimal('cost_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->unsignedInteger('weight_grams')->nullable()->comment('Null = inherit from parent');
            $table->boolean('is_active')->default(true);
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->onDelete('set null');
            $table->timestamps();

            $table->index('product_id');
            $table->index('sku');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
```

### 8.3 Add variant support to inventory_balances

**File:** `database/migrations/YYYY_MM_DD_add_variant_to_inventory_balances.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            // Drop old unique key
            $table->dropUnique(['product_id', 'location_id']);

            // Add variant column
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->onDelete('cascade');

            // Add new composite unique key
            $table->unique(['product_id', 'product_variant_id', 'location_id'], 'unique_product_variant_location');

            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->dropUnique('unique_product_variant_location');
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
            $table->unique(['product_id', 'location_id']);
        });
    }
};
```

### 8.4 Add variant support to order_items

**File:** `database/migrations/YYYY_MM_DD_add_variant_to_order_items.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->onDelete('restrict');

            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
        });
    }
};
```

### 8.5 Add variant support to purchase_items

**File:** `database/migrations/YYYY_MM_DD_add_variant_to_purchase_items.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->onDelete('restrict');

            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
        });
    }
};
```

### 8.6 Add variant support to stock_movements

**File:** `database/migrations/YYYY_MM_DD_add_variant_to_stock_movements.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->onDelete('restrict');

            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
        });
    }
};
```

---

## 9. UI Implementation (High-Level)

### 9.1 Product Create/Edit Form - Tab Structure

**New Tab: "Variasi"** (alongside General, Harga, Stok tabs)

**When Tab is Opened:**
- Show button: **"+ Variasi"** (add new variant group)
- Show checkboxes for enabling:
  - ☑ **Harga Bervariasi** (enables Harga tab)
  - ☑ **Stock Bervariasi** (enables Stok tab)
  - ☑ **Berat Bervariasi** (optional, enables weight override)

**Variant Groups Builder:**

Similar to screenshot you provided, user builds variant groups:

```
┌─────────────────────────────────────────────────┐
│ [+ Variasi]  ☑ Harga Bervariasi  ☑ Stock Bervariasi  ☑ Berat Bervariasi │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ Grup Variasi: Ukuran                      [×]   │
│ ═══════════════════════════════════════════════ │
│ ≡ M                                      [⋮]    │
│ ≡ L                                      [⋮]    │
│ ≡ XL                                     [⋮]    │
│ ≡ XXL                                    [⋮]    │
│ [+ Opsi]                                        │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ Grup Variasi: Warna                       [×]   │
│ ═══════════════════════════════════════════════ │
│ ≡ Merah                                  [⋮]    │
│ ≡ Biru                                   [⋮]    │
│ ≡ Hijau                                  [⋮]    │
│ [+ Opsi]                                        │
└─────────────────────────────────────────────────┘
```

**Features:**
- Add/remove variant groups
- Add/remove options per group
- Drag to reorder options
- Delete menu (⋮) per option

**JavaScript Component:** `product-variants-builder.js`

---

### 9.2 Tab "Harga" - Bulk Pricing Helper

**Only visible when "Harga Bervariasi" is checked**

**Purpose:** Mempermudah input harga untuk multiple kombinasi sekaligus

**UI Matrix Example (2 groups: Ukuran × Warna):**

```
Ukuran | Variasi 2      | Harga Modal | Harga Jual
───────┼────────────────┼─────────────┼────────────
M      | Semua warna    | Rp 50.000   | Rp 100.000
L      | Semua warna    | Rp 55.000   | Rp 110.000
XL     | Semua warna    | Rp 60.000   | Rp 120.000
XXL    | Semua warna    | Rp 65.000   | Rp 130.000
```

**How It Works:**
- User set harga untuk "M + Semua warna"
- System **apply ke semua kombinasi** M+Merah, M+Biru, M+Hijau
- User bisa override harga spesifik di matrix detail (optional advanced view)

**Alternative View - Full Matrix:**

```
Ukuran | Warna  | Harga Modal | Harga Jual
───────┼────────┼─────────────┼────────────
M      | Merah  | Rp 50.000   | Rp 100.000
M      | Biru   | Rp 50.000   | Rp 100.000
M      | Hijau  | Rp 50.000   | Rp 100.000
L      | Merah  | Rp 55.000   | Rp 110.000
L      | Biru   | Rp 55.000   | Rp 110.000
...
```

**JavaScript Component:** `product-variants-pricing.js`

---

### 9.3 Tab "Stok" - Stock Input Matrix

**Only visible when "Stock Bervariasi" is checked**

**UI Matrix:**

```
Ukuran | Warna  | Kode Stok (SKU) | Stok
───────┼────────┼─────────────────┼──────
M      | Merah  | KAOS-001-1      | 18
M      | Biru   | KAOS-001-2      | 11
M      | Hijau  | KAOS-001-3      | 13
L      | Merah  | KAOS-001-4      | 10
L      | Biru   | KAOS-001-5      | 10
...
```

**Features:**
- SKU auto-generated (read-only, can be manually edited)
- Stock input per combination
- Can be left empty (stock added via Purchase later)

**JavaScript Component:** `product-variants-stock.js`

---

### 9.4 Backend Processing

**When form submitted:**

1. **Parse variant_groups** from builder:
```json
[
  {"name": "Ukuran", "options": ["M", "L", "XL", "XXL"]},
  {"name": "Warna", "options": ["Merah", "Biru", "Hijau"]}
]
```

2. **Generate Cartesian product** (all combinations):
```
M × Merah
M × Biru
M × Hijau
L × Merah
L × Biru
... (12 combinations total)
```

3. **Create product_variants records:**
- For each combination, create variant with:
  - `sku`: auto-generated (KAOS-001-1, KAOS-001-2, ...)
  - `variant_attributes`: `{"Ukuran": "M", "Warna": "Merah"}`
  - `cost_price`: from pricing matrix (or bulk pricing)
  - `selling_price`: from pricing matrix
  - `weight_grams`: if berat bervariasi, else NULL (inherit from parent)

4. **Create inventory_balances** (if stock provided):
- For each variant with stock > 0
- Link to default warehouse location

### 9.5 Product Detail View

**Add Variants Tab:**
- Show list of all variants with:
  - SKU
  - Attributes (Warna: Merah, Ukuran: M)
  - Cost Price
  - Selling Price
  - Weight
  - Stock (total across locations)
  - Status (Active/Inactive)
  - Actions (Edit, Delete/Deactivate)

### 9.6 Order Form - Product Selection

**Variant Dropdown:**
- When selecting product with variants:
  - Show dropdown with all active variants
  - Display: `Variant Attributes - Rp Price (Stock: X)`
  - Example: "Ukuran: M, Warna: Merah - Rp 100.000 (Stock: 18)"
  - On select: populate price, check stock availability

### 9.7 Purchase Form - Product Selection

**Variant Dropdown:**
- Same as Order form
- When receiving: specify which variant quantity

---

## 10. Validation Rules

### 10.1 Product Validation

```php
// When has_variants = true
'has_variants' => 'boolean',
'variant_groups' => 'required_if:has_variants,true|array|min:1',
'variant_groups.*.name' => 'required|string',
'variant_groups.*.options' => 'required|array|min:1',
'variant_groups.*.options.*' => 'required|string',
'cost_price' => 'nullable|required_if:has_variants,false|numeric|min:0',
'selling_price' => 'nullable|required_if:has_variants,false|numeric|min:0',
```

### 10.2 Order Item Validation

```php
'product_id' => 'required|exists:products,id',
'product_variant_id' => [
    'nullable',
    'exists:product_variants,id',
    function ($attribute, $value, $fail) use ($request) {
        $product = Product::find($request->product_id);
        if ($product && $product->has_variants && !$value) {
            $fail('Variant harus dipilih untuk produk ini.');
        }
        if ($product && !$product->has_variants && $value) {
            $fail('Produk ini tidak memiliki variant.');
        }
    },
],
```

---

## 11. Testing Strategy

### 11.1 Unit Tests

**ProductVariant Model:**
- `test_sku_generation()`
- `test_display_name_attribute()`
- `test_effective_weight()`
- `test_can_be_deleted_with_transactions()`
- `test_can_be_deleted_without_transactions()`

**Product Model:**
- `test_can_have_variants_without_inventory()`
- `test_cannot_have_variants_with_inventory()`
- `test_get_total_stock_for_variants()`

### 11.2 Feature Tests

**Variant CRUD:**
- `test_create_variants_for_product()`
- `test_update_variant()`
- `test_delete_variant_without_transactions()`
- `test_cannot_delete_variant_with_orders()`
- `test_variant_auto_sku_generation()`

**Inventory with Variants:**
- `test_stock_movement_for_variant()`
- `test_stock_balance_per_variant()`
- `test_order_reduces_variant_stock()`
- `test_purchase_increases_variant_stock()`

### 11.3 Integration Tests

- `test_full_flow_create_product_with_variants()`
- `test_full_flow_order_with_variants()`
- `test_full_flow_purchase_with_variants()`
- `test_convert_simple_to_variable_product()`

---

## 12. Data Migration Strategy

### 12.1 Existing Products

All existing products will remain as **simple products** (`has_variants = false`).

**No automatic migration needed** - they continue working as-is.

### 12.2 Converting Existing Product to Have Variants

**Manual Process:**
1. Product must have NO existing inventory (`inventory_balances.product_variant_id IS NULL`)
2. Product must have NO existing orders (`order_items.product_variant_id IS NULL`)
3. User clicks "Add Variants" button
4. System validates constraints
5. User creates variants
6. System sets `has_variants = true`

**If product has inventory/orders:**
- Show warning: "Cannot add variants. Product has existing inventory or orders."
- Suggest: Create new product with variants instead

---

## 13. Implementation Phases

### Phase 1: Database & Models (Week 1)
- [ ] Create migrations
- [ ] Run migrations
- [ ] Create ProductVariant model
- [ ] Update Product model
- [ ] Update related models (OrderItem, PurchaseItem, etc.)

### Phase 2: Service Layer (Week 1-2)
- [ ] Update InventoryService
- [ ] Update Event Handlers
- [ ] Add validation rules
- [ ] Write unit tests

### Phase 3: Controllers & Routes (Week 2)
- [ ] Create ProductVariantController
- [ ] Update ProductController
- [ ] Add routes
- [ ] Write feature tests

### Phase 4: UI - Product Management (Week 3)
- [ ] Variant manager component (JS)
- [ ] Product create form with variants
- [ ] Product edit form with variants
- [ ] Product detail view with variants tab
- [ ] Variant CRUD interface

### Phase 5: UI - Transactions (Week 4)
- [ ] Order form variant selector
- [ ] Purchase form variant selector
- [ ] Inventory view with variants
- [ ] Stock movement with variants

### Phase 6: Testing & Polish (Week 5)
- [ ] Integration tests
- [ ] Manual testing
- [ ] Bug fixes
- [ ] Documentation
- [ ] User training

---

## 14. Risks & Mitigation

### Risk 1: Complex UI for Variant Matrix

**Risk:** Creating UI for N×M attribute combinations is complex

**Mitigation:**
- Start with simple 2-attribute matrix (Ukuran × Warna)
- Use JavaScript component similar to `custom-fields-repeater.js`
- Allow bulk price setting (apply same price to all)
- Allow CSV import for large matrices

### Risk 2: Performance with Many Variants

**Risk:** Product with 100+ variants may slow down queries

**Mitigation:**
- Add database indexes on `product_variant_id`
- Use eager loading in queries
- Paginate variant list in UI
- Cache variant counts

### Risk 3: Backward Compatibility

**Risk:** Breaking existing inventory/order flows

**Mitigation:**
- Keep all existing code paths working
- Use nullable `product_variant_id` columns
- Add conditional logic: if variant exists, use variant logic, else use product logic
- Extensive testing on existing data

### Risk 4: SKU Collision

**Risk:** Auto-generated SKU might collide

**Mitigation:**
- Check SKU uniqueness before insert
- Retry with next sequence number if collision
- Allow manual SKU override
- Database unique constraint as final guard

---

## 15. Future Enhancements

### Phase 2 Features (Post-MVP)

1. **Bulk Variant Operations**
   - Bulk price update
   - Bulk activate/deactivate
   - CSV export/import

2. **Variant-Specific Discounts**
   - Promo pricing per variant
   - Bundle deals with variants

3. **Variant Analytics**
   - Best-selling variants
   - Slow-moving variant detection
   - Variant profitability report

4. **Advanced Variant Features**
   - Variant-specific custom fields
   - Variant-specific gallery (multiple images per variant)
   - Variant dependencies (e.g., Size L not available in Red)

---

## 16. References

- Laravel Eloquent Relationships: https://laravel.com/docs/eloquent-relationships
- JSON Column Casting: https://laravel.com/docs/eloquent-mutators#array-and-json-casting
- Database Migrations: https://laravel.com/docs/migrations
- Event-Driven Architecture: See `02-warehouse_inventory_system_blueprint.md`
- Custom Fields Implementation: See previous custom fields work

---

## Appendix A: Example Variant Data Structure

### Example 1: Kaos with 2 Attributes

**Product:**
- SKU: `KAOS-001`
- Name: `Kaos Polos Premium`
- has_variants: `true`

**Variants:**
```json
[
  {
    "sku": "KAOS-001-1",
    "variant_attributes": {"Ukuran": "M", "Warna": "Merah"},
    "cost_price": 50000,
    "selling_price": 100000,
    "weight_grams": null
  },
  {
    "sku": "KAOS-001-2",
    "variant_attributes": {"Ukuran": "L", "Warna": "Merah"},
    "cost_price": 50000,
    "selling_price": 100000,
    "weight_grams": null
  },
  {
    "sku": "KAOS-001-3",
    "variant_attributes": {"Ukuran": "XL", "Warna": "Merah"},
    "cost_price": 60000,
    "selling_price": 120000,
    "weight_grams": 250
  },
  {
    "sku": "KAOS-001-4",
    "variant_attributes": {"Ukuran": "M", "Warna": "Biru"},
    "cost_price": 50000,
    "selling_price": 100000,
    "weight_grams": null
  }
]
```

### Example 2: Simple Product (No Variants)

**Product:**
- SKU: `BUKU-001`
- Name: `Buku Laravel Advanced`
- has_variants: `false`
- cost_price: `75000`
- selling_price: `150000`
- weight_grams: `500`

**Variants:** (none)

---

## Appendix B: Database Query Examples

### Get all variants with stock for a product

```sql
SELECT
    pv.sku,
    pv.variant_attributes,
    pv.cost_price,
    pv.selling_price,
    SUM(ib.qty_on_hand) as total_stock,
    pv.is_active
FROM product_variants pv
LEFT JOIN inventory_balances ib ON ib.product_variant_id = pv.id
WHERE pv.product_id = ?
GROUP BY pv.id
ORDER BY pv.sku;
```

### Get total stock for product (variants or simple)

```sql
-- For variable product
SELECT SUM(ib.qty_on_hand) as total_stock
FROM inventory_balances ib
WHERE ib.product_id = ?
  AND ib.product_variant_id IS NOT NULL;

-- For simple product
SELECT SUM(ib.qty_on_hand) as total_stock
FROM inventory_balances ib
WHERE ib.product_id = ?
  AND ib.product_variant_id IS NULL;
```

### Get order items with variant info

```sql
SELECT
    oi.id,
    p.name as product_name,
    pv.variant_attributes,
    oi.quantity,
    oi.price
FROM order_items oi
JOIN products p ON p.id = oi.product_id
LEFT JOIN product_variants pv ON pv.id = oi.product_variant_id
WHERE oi.order_id = ?;
```

---

**End of Development Guide**
