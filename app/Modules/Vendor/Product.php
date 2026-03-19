<?php

namespace App\Modules\Vendor;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasUlids, InteractsWithMedia;

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
        'images' => 'array', // Mantener por compatibilidad temporal
        'attributes' => 'array',
        'tags' => 'array',
        'status' => 'string',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Definir las colecciones de media para el producto.
     */
    public function registerMediaCollections(): void
    {
        // Imagen destacada (solo una)
        $this->addMediaCollection('featured-image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
            ->useDisk('public');

        // Galería de imágenes (múltiples)
        $this->addMediaCollection('product-gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
            ->useDisk('public');
    }

    /**
     * Definir las conversiones de imágenes.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // Para imagen destacada
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('featured-image', 'product-gallery');

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('featured-image', 'product-gallery');

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(1200)
            ->sharpen(10)
            ->performOnCollections('featured-image', 'product-gallery');

        // Para galería - formato WebP optimizado
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(80)
            ->performOnCollections('featured-image', 'product-gallery');
    }

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
     * Obtener URL de la imagen destacada usando MediaLibrary.
     */
    public function getFeaturedImageUrl(): ?string
    {
        // Priorizar MediaLibrary sobre el campo legacy
        if ($this->hasMedia('featured-image')) {
            return $this->getFirstMediaUrl('featured-image', 'medium');
        }

        // Fallback al sistema antiguo por compatibilidad
        if (! $this->featured_image) {
            return null;
        }

        return \App\Helpers\ImageHelper::getImageUrl($this->featured_image);
    }

    /**
     * Obtener todas las URLs de las imágenes del producto usando MediaLibrary.
     */
    public function getImageUrls(): array
    {
        // Priorizar MediaLibrary
        if ($this->hasMedia('product-gallery')) {
            return $this->getMedia('product-gallery')
                ->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl('medium'),
                    'thumb' => $media->getUrl('thumb'),
                    'large' => $media->getUrl('large'),
                    'webp' => $media->getUrl('webp'),
                    'name' => $media->name,
                    'order' => $media->order_column,
                ])
                ->sortBy('order')
                ->values()
                ->toArray();
        }

        // Fallback al sistema antiguo por compatibilidad
        if (! $this->images) {
            return [];
        }

        return collect($this->images)->map(fn ($image) => [
            'url' => \App\Helpers\ImageHelper::getImageUrl($image),
            'thumb' => \App\Helpers\ImageHelper::getImageUrl($image),
            'large' => \App\Helpers\ImageHelper::getImageUrl($image),
            'name' => basename($image),
            'order' => 0,
        ])->toArray();
    }

    /**
     * Obtener todas las imágenes para el carrusel (incluye destacada).
     */
    public function getCarouselImages(): array
    {
        $images = [];

        // Agregar imagen destacada primero si existe
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

        // Agregar resto de la galería
        $galleryImages = $this->getImageUrls();
        $images = array_merge($images, $galleryImages);

        return $images;
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
