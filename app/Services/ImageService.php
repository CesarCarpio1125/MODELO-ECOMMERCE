<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageService
{
    /**
     * Get images for product with Base64 conversion for NativePHP
     */
    public static function getProductImages($product)
    {
        $images = [];
        
        try {
            // Get MediaLibrary images
            $galleryMedia = Media::where('model_type', 'App\Modules\Vendor\Product')
                ->where('model_id', $product->id)
                ->where('collection_name', 'product-gallery')
                ->orderBy('order_column', 'asc')
                ->get();

            foreach ($galleryMedia as $index => $media) {
                $customProperties = is_array($media->custom_properties) 
                    ? $media->custom_properties 
                    : json_decode($media->custom_properties ?? '{}', true);
                
                // Construir paths para MediaLibrary
                $originalPath = $media->id . '/' . $media->file_name;
                $mediumPath = $media->id . '/conversions/' . str_replace('.jpg', '-medium.jpg', $media->file_name);
                $thumbPath = $media->id . '/conversions/' . str_replace('.jpg', '-thumb.jpg', $media->file_name);
                $largePath = $media->id . '/conversions/' . str_replace('.jpg', '-large.jpg', $media->file_name);
                
                $images[] = [
                    'id' => $media->id,
                    'url' => \App\Helpers\ImageHelper::getImageAsBase64($mediumPath) ?? \App\Helpers\ImageHelper::getImageUrl($mediumPath),
                    'thumb' => \App\Helpers\ImageHelper::getImageAsBase64($thumbPath) ?? \App\Helpers\ImageHelper::getImageUrl($thumbPath),
                    'large' => \App\Helpers\ImageHelper::getImageAsBase64($largePath) ?? \App\Helpers\ImageHelper::getImageUrl($largePath),
                    'webp' => null, // Could be implemented later
                    'name' => $customProperties['name'] ?? $media->file_name,
                    'order' => $customProperties['order'] ?? $index + 1,
                ];
            }
            
            // Priority 2: Fallback to legacy images
            if (empty($images) && $product->images) {
                foreach ($product->images as $index => $image) {
                    $images[] = [
                        'id' => 'legacy-' . $index,
                        'url' => \App\Helpers\ImageHelper::getImageAsBase64($image) ?? \App\Helpers\ImageHelper::getImageUrl($image),
                        'thumb' => \App\Helpers\ImageHelper::getImageAsBase64($image) ?? \App\Helpers\ImageHelper::getImageUrl($image),
                        'large' => \App\Helpers\ImageHelper::getImageAsBase64($image) ?? \App\Helpers\ImageHelper::getImageUrl($image),
                        'webp' => null,
                        'name' => $image,
                        'order' => $index + 1,
                    ];
                }
            }
            
        } catch (\Exception $e) {
            Log::error('ImageService: Failed to get product images', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $images;
    }
    
    /**
     * Get featured image for product with Base64 conversion for NativePHP
     */
    public static function getFeaturedImage($product)
    {
        try {
            // Get MediaLibrary featured image
            $featuredMedia = Media::where('model_type', 'App\Modules\Vendor\Product')
                ->where('model_id', $product->id)
                ->where('collection_name', 'featured-image')
                ->first();

            if ($featuredMedia) {
                $path = $featuredMedia->id . '/' . $featuredMedia->file_name;
                return \App\Helpers\ImageHelper::getImageAsBase64($path) ?? \App\Helpers\ImageHelper::getImageUrl($path);
            }

            // Fallback to legacy field
            if ($product->featured_image) {
                return \App\Helpers\ImageHelper::getImageAsBase64($product->featured_image) ?? \App\Helpers\ImageHelper::getImageUrl($product->featured_image);
            }

        } catch (\Exception $e) {
            Log::error('ImageService: Failed to get featured image', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
}
