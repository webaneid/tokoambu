<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'category_id',
        'supplier_id',
        'cost_price',
        'selling_price',
        'weight_grams',
        'stock',
        'is_active',
        'allow_preorder',
        'featured_media_id',
        'custom_field_values',
        'has_variants',
        'variant_groups',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'weight_grams' => 'integer',
        'is_active' => 'boolean',
        'allow_preorder' => 'boolean',
        'custom_field_values' => 'array',
        'has_variants' => 'boolean',
        'variant_groups' => 'array',
    ];

    protected $appends = ['qty_on_hand'];

    /**
     * Boot the model and auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug when creating new product
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
        });

        // Auto-update slug when product name changes or slug is empty
        static::updating(function ($product) {
            // Generate slug if it's empty (null or empty string)
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            }
        });
    }

    /**
     * Generate unique slug for product
     */
    protected static function generateUniqueSlug(string $name, ?int $id = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        // Check if slug already exists (excluding current product if updating)
        while (static::where('slug', $slug)
            ->when($id, fn($query) => $query->where('id', '!=', $id))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, OrderItem::class, 'product_id', 'id', 'id', 'order_id');
    }

    public function preorderPeriods()
    {
        return $this->hasMany(PreorderPeriod::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function profit()
    {
        return $this->selling_price - $this->cost_price;
    }

    public function inventoryBalances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class);
    }

    public function supplierPrices(): HasMany
    {
        return $this->hasMany(ProductSupplierPrice::class);
    }

    public function getQtyOnHandAttribute(): float
    {
        return (float) ($this->inventoryBalances()->sum('qty_on_hand') ?? 0);
    }

    public function featuredMedia()
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function galleryMedia()
    {
        return $this->hasMany(Media::class, 'product_id')
            ->where('type', 'product_photo')
            ->orderBy('gallery_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    /**
     * Check if this product is a simple product (no variants).
     */
    public function isSimpleProduct(): bool
    {
        return !$this->has_variants;
    }

    /**
     * Check if this product has variants.
     */
    public function isVariableProduct(): bool
    {
        return $this->has_variants;
    }

    /**
     * Get total stock for simple product or sum of all variant stocks.
     */
    public function getTotalStockAttribute(): float
    {
        if ($this->isSimpleProduct()) {
            return $this->qty_on_hand;
        }

        return $this->variants()->get()->sum('total_stock');
    }
}
