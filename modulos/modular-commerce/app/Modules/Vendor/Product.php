<?php

namespace App\Modules\Vendor;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUlids;

    protected $fillable = [
        'vendor_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_price',
        'cost_price',
        'sku',
        'barcode',
        'stock_quantity',
        'min_stock_level',
        'track_stock',
        'is_active',
        'is_featured',
        'weight',
        'dimensions',
        'status',
        'featured_image',
        'images',
        'attributes',
        'category_id',
        'created_by',
        'tags',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'images' => 'array',
        'attributes' => 'array',
        'tags' => 'array',
        'status' => 'string',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->is_active;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= ($this->min_stock_level ?? 5) && $this->stock_quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Get the featured image URL.
     */
    public function getFeaturedImageUrl(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        return \App\Helpers\ImageHelper::getImageUrl($this->featured_image);
    }

    /**
     * Get all product image URLs.
     */
    public function getImageUrls(): array
    {
        if (! $this->images) {
            return [];
        }

        return collect($this->images)->map(fn ($image) => \App\Helpers\ImageHelper::getImageUrl($image))->toArray();
    }

    /**
     * Get the featured image path for storage.
     */
    public function getFeaturedImagePath(): string
    {
        return 'products/'.$this->id.'/featured_image';
    }

    /**
     * Get the images path for storage.
     */
    public function getImagesPath(): string
    {
        return 'products/'.$this->id.'/images';
    }

    /**
     * Generate unique SKU.
     */
    public static function generateSku(string $productName): string
    {
        $base = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $productName), 0, 8));
        $random = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
        
        return $base . '-' . $random;
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
