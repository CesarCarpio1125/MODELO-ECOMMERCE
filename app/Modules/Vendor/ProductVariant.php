<?php

namespace App\Modules\Vendor;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasUlids;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'stock',
        'weight',
        'attributes',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'attributes' => 'array',
        'status' => 'string',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Get the variant image URL.
     */
    public function getImageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        // Use ImageHelper to generate proper URL for both web and NativePHP environments
        return \App\Helpers\ImageHelper::getImageUrl($this->image);
    }

    /**
     * Get formatted attributes for display.
     */
    public function getFormattedAttributes(): string
    {
        if (! $this->attributes) {
            return '';
        }

        $formatted = [];
        foreach ($this->attributes as $key => $value) {
            $formatted[] = ucfirst($key) . ': ' . $value;
        }

        return implode(', ', $formatted);
    }
}
