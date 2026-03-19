<?php

namespace App\Modules\Vendor;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasUlids, InteractsWithMedia;

    /**
     * Register media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('featured-image')
            ->singleFile()
            ->accepts('image/*')
            ->useDisk('public');

        $this
            ->addMediaCollection('product-gallery')
            ->accepts('image/*')
            ->useDisk('public');
    }

    /**
     * Register media conversions for the model.
     */
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media|null $media = null): void
    {
        if (!$media) {
            return;
        }

        // Featured image conversions
        if ($media->collection_name === 'featured-image') {
            $this->addMediaConversion('thumb')
                ->width(150)
                ->height(150)
                ->sharpen(10)
                ->performOnCollections('featured-image');

            $this->addMediaConversion('medium')
                ->width(600)
                ->height(600)
                ->sharpen(10)
                ->performOnCollections('featured-image');

            $this->addMediaConversion('large')
                ->width(1200)
                ->height(1200)
                ->sharpen(10)
                ->performOnCollections('featured-image');

            $this->addMediaConversion('webp')
                ->format('webp')
                ->quality(80)
                ->performOnCollections('featured-image');
        }

        // Gallery image conversions
        if ($media->collection_name === 'product-gallery') {
            $this->addMediaConversion('thumb')
                ->width(150)
                ->height(150)
                ->sharpen(10)
                ->performOnCollections('product-gallery');

            $this->addMediaConversion('medium')
                ->width(600)
                ->height(600)
                ->sharpen(10)
                ->performOnCollections('product-gallery');

            $this->addMediaConversion('large')
                ->width(1200)
                ->height(1200)
                ->sharpen(10)
                ->performOnCollections('product-gallery');

            $this->addMediaConversion('webp')
                ->format('webp')
                ->quality(80)
                ->performOnCollections('product-gallery');
        }
    }

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
        // Priority 1: Use MediaLibrary with API endpoint (direct DB query)
        $featuredMedia = DB::table('media')
            ->where('model_type', 'App\Modules\Vendor\Product')
            ->where('model_id', $this->id)
            ->where('collection_name', 'featured-image')
            ->first();

        if ($featuredMedia) {
            return url("/api/images/vendors/{$this->vendor_id}/{$featuredMedia->file_name}");
        }

        // Priority 2: Fallback to legacy
        if (! $this->featured_image) {
            return null;
        }

        return \App\Helpers\ImageHelper::getImageUrl($this->featured_image);
    }

    /**
     * Get the featured image URL using API endpoint.
     */
    public function getFeaturedImageUrlApi(): ?string
    {
        if ($this->hasMedia('featured-image')) {
            $media = $this->getFirstMedia('featured-image');
            return url("/api/images/vendors/{$this->vendor_id}/{$media->file_name}");
        }

        return $this->getFeaturedImageUrl(); // Fallback to legacy
    }

    /**
     * Get all product image URLs.
     */
    public function getImageUrls(): array
    {
        $images = [];

        // Priority 1: Use MediaLibrary with API endpoint (direct DB query)
        $galleryMedia = DB::table('media')
            ->where('model_type', 'App\Modules\Vendor\Product')
            ->where('model_id', $this->id)
            ->where('collection_name', 'product-gallery')
            ->orderBy('order_column', 'asc')
            ->get();

        foreach ($galleryMedia as $index => $media) {
            $customProperties = json_decode($media->custom_properties ?? '{}', true);
            $images[] = [
                'id' => $media->id,
                'url' => url("/api/images/vendors/{$this->vendor_id}/{$media->file_name}"),
                'thumb' => url("/api/images/vendors/{$this->vendor_id}/{$media->file_name}"),
                'large' => url("/api/images/vendors/{$this->vendor_id}/{$media->file_name}"),
                'webp' => null, // Could be implemented later
                'name' => $media->file_name,
                'order' => $customProperties['order'] ?? $index + 1,
            ];
        }

        // Priority 2: Fallback to legacy images
        if (empty($images) && $this->images) {
            foreach ($this->images as $index => $image) {
                $images[] = [
                    'id' => 'legacy-' . $index,
                    'url' => \App\Helpers\ImageHelper::getImageUrl($image),
                    'thumb' => \App\Helpers\ImageHelper::getImageUrl($image),
                    'large' => \App\Helpers\ImageHelper::getImageUrl($image),
                    'webp' => null,
                    'name' => basename($image),
                    'order' => $index + 1,
                ];
            }
        }

        return $images;
    }

    /**
     * Get all product image URLs using API endpoint.
     */
    public function getImageUrlsApi(): array
    {
        $images = [];

        // Get MediaLibrary images
        if ($this->hasMedia('product-gallery')) {
            foreach ($this->getMedia('product-gallery') as $index => $media) {
                $images[] = [
                    'id' => $media->id,
                    'url' => url("/api/images/vendors/{$this->vendor_id}/{$media->file_name}"),
                    'thumb' => url("/api/images/vendors/{$this->vendor_id}/{$media->file_name}"),
                    'large' => url("/api/images/vendors/{$this->vendor_id}/{$media->file_name}"),
                    'webp' => null, // Could be implemented later
                    'name' => $media->file_name,
                    'order' => $media->getCustomProperty('order', $index + 1),
                ];
            }
        }

        // Fallback to legacy images if no MediaLibrary images
        if (empty($images) && $this->images) {
            foreach ($this->images as $index => $image) {
                $images[] = [
                    'id' => 'legacy-' . $index,
                    'url' => \App\Helpers\ImageHelper::getImageUrl($image),
                    'thumb' => \App\Helpers\ImageHelper::getImageUrl($image),
                    'large' => \App\Helpers\ImageHelper::getImageUrl($image),
                    'webp' => null,
                    'name' => basename($image),
                    'order' => $index + 1,
                ];
            }
        }

        return $images;
    }

    /**
     * Get standardized images structure for frontend.
     * Returns consistent format for both legacy and MediaLibrary images.
     */
    public function getImagesForFrontend(): array
    {
        $images = [];

        // Add featured image first if exists
        if ($featuredUrl = $this->getFeaturedImageUrl()) {
            $images[] = [
                'id' => 'featured',
                'url' => $featuredUrl,
                'thumb' => $this->hasMedia('featured-image') 
                    ? $this->getFirstMediaUrl('featured-image', 'thumb')
                    : $featuredUrl,
                'large' => $this->hasMedia('featured-image') 
                    ? $this->getFirstMediaUrl('featured-image', 'large')
                    : $featuredUrl,
                'webp' => $this->hasMedia('featured-image') 
                    ? $this->getFirstMediaUrl('featured-image', 'webp')
                    : null,
                'name' => 'Featured Image',
                'order' => -1,
            ];
        }

        // Add gallery images
        $galleryImages = $this->getImageUrls();
        $images = array_merge($images, $galleryImages);

        return $images;
    }

    /**
     * Get carousel images combining featured and gallery images.
     * Uses API-compatible URLs for frontend compatibility.
     */
    public function getCarouselImages(): array
    {
        $images = [];

        // Add featured image first if exists
        if ($featuredUrl = $this->getFeaturedImageUrl()) {
            $images[] = [
                'id' => 'featured',
                'url' => $featuredUrl,
                'thumb' => $featuredUrl,
                'large' => $featuredUrl,
                'webp' => null,
                'name' => 'Featured Image',
                'order' => -1,
            ];
        }

        // Add gallery images using API URLs
        $galleryImages = $this->getImageUrls();
        $images = array_merge($images, $galleryImages);

        return $images;
    }

    /**
     * Get carousel images using API endpoint for frontend compatibility.
     */
    public function getCarouselImagesApi(): array
    {
        $images = [];

        // Add featured image first if exists
        if ($featuredUrl = $this->getFeaturedImageUrlApi()) {
            $images[] = [
                'id' => 'featured',
                'url' => $featuredUrl,
                'thumb' => $featuredUrl,
                'large' => $featuredUrl,
                'webp' => null,
                'name' => 'Featured Image',
                'order' => -1,
            ];
        }

        // Add gallery images using API URLs
        $galleryImages = $this->getImageUrlsApi();
        $images = array_merge($images, $galleryImages);

        return $images;
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
