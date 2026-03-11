<?php

namespace App\Modules\Vendor;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'description',
        'status',
        'store_image',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['store_image_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Get the store image URL accessor.
     */
    public function getStoreImageUrlAttribute(): ?string
    {
        if (! $this->store_image) {
            return null;
        }

        // Use ImageHelper to generate proper URL with cache-busting
        // Use model's updated_at timestamp for cache-busting instead of current time
        $timestamp = $this->updated_at?->timestamp ?? $this->created_at->timestamp;
        return \App\Helpers\ImageHelper::getImageUrl($this->store_image, $timestamp);
    }

    /**
     * Get the store image path for storage.
     */
    public function getStoreImagePath(): string
    {
        return 'vendors/'.$this->id.'/store_image';
    }

    /**
     * Check if vendor has a store image.
     */
    public function hasStoreImage(): bool
    {
        return ! empty($this->store_image);
    }
}
