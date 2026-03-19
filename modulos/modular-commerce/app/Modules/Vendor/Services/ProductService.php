<?php

namespace App\Modules\Vendor\Services;

use App\Modules\Vendor\Product;
use App\Modules\Vendor\ProductVariant;
use App\Modules\Vendor\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function createProduct(Vendor $vendor, array $data): Product
    {
        $product = new Product([
            'vendor_id' => $vendor->id,
            'name' => $data['name'],
            'slug' => Product::generateSlug($data['name']),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'] ?? $data['stock'] ?? 0,
            'sku' => $data['sku'] ?? Product::generateSku($data['name']),
            'weight' => $data['weight'] ?? null,
            'dimensions' => $data['dimensions'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'category_id' => $data['category_id'] ?? null,
            'tags' => $data['tags'] ?? [],
            'created_by' => auth()->id(),
        ]);

        $product->save();

        // Handle featured image upload
        if (isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            $this->uploadFeaturedImage($product, $data['featured_image']);
        }

        // Handle multiple image uploads
        if (isset($data['images']) && is_array($data['images'])) {
            $this->uploadProductImages($product, $data['images']);
        }

        // Create variants if provided
        if (isset($data['variants']) && is_array($data['variants'])) {
            $this->createProductVariants($product, $data['variants']);
        }

        return $product->fresh();
    }

    public function updateProduct(Product $product, array $data): Product
    {
        // Enhanced logging for debugging
        \Log::info('ProductService: updateProduct called', [
            'product_id' => $product->id,
            'data_keys' => array_keys($data),
            'has_featured_image' => isset($data['featured_image']),
            'featured_image_type' => isset($data['featured_image']) ? get_class($data['featured_image']) : 'not_set',
            'has_images' => isset($data['images']),
            'images_type' => isset($data['images']) ? gettype($data['images']) : 'not_set',
            'images_count' => isset($data['images']) && is_array($data['images']) ? count($data['images']) : 0
        ]);
        
        $product->update([
            'name' => $data['name'] ?? $product->name,
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price'] ?? $product->price,
            'stock_quantity' => $data['stock_quantity'] ?? $data['stock'] ?? $product->stock_quantity,
            'weight' => $data['weight'] ?? $product->weight,
            'dimensions' => $data['dimensions'] ?? $product->dimensions,
            'status' => $data['status'] ?? $product->status,
            'category_id' => $data['category_id'] ?? $product->category_id,
            'tags' => $data['tags'] ?? $product->tags,
        ]);

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $product->slug = Product::generateSlug($data['name']);
            $product->save();
        }

        // Handle featured image upload
        if (isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            \Log::info('ProductService: Processing featured image', [
                'product_id' => $product->id,
                'filename' => $data['featured_image']->getClientOriginalName(),
                'size' => $data['featured_image']->getSize(),
                'mime_type' => $data['featured_image']->getMimeType()
            ]);
            $this->uploadFeaturedImage($product, $data['featured_image']);
        }

        // Handle multiple image uploads
        if (isset($data['images']) && is_array($data['images'])) {
            \Log::info('ProductService: Calling uploadProductImages from updateProduct', [
                'product_id' => $product->id,
                'images_array' => $data['images']
            ]);
            $this->uploadProductImages($product, $data['images']);
        }

        // Update variants if provided
        if (isset($data['variants']) && is_array($data['variants'])) {
            $this->updateProductVariants($product, $data['variants']);
        }

        \Log::info('ProductService: updateProduct completed', [
            'product_id' => $product->id,
            'featured_image_after' => $product->getFeaturedImageUrl(),
            'images_after' => $product->getImageUrls()
        ]);

        return $product->fresh();
    }

    public function deleteProduct(Product $product): bool
    {
        // Delete product images from storage
        $this->deleteProductImages($product);

        // Delete variants
        $product->variants()->delete();

        // Delete product
        return $product->delete();
    }

    public function getVendorProducts(Vendor $vendor, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $vendor->products();

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    private function uploadFeaturedImage(Product $product, UploadedFile $image): void
    {
        // Force MediaLibrary usage - NO LEGACY FALLBACK
        $product->addMedia($image)
            ->usingFileName($image->getClientOriginalName())
            ->withCustomProperties(['type' => 'featured', 'uploaded_at' => now()])
            ->toMediaCollection('featured-image', 'public');
    }

    private function uploadProductImages(Product $product, array $images): void
    {
        \Log::info('ProductService: Starting multiple images upload with MediaLibrary', [
            'product_id' => $product->id,
            'images_count' => count($images),
            'images_data' => array_map(function($img) {
                return $img instanceof UploadedFile ? [
                    'name' => $img->getClientOriginalName(),
                    'size' => $img->getSize(),
                    'mime' => $img->getMimeType(),
                    'error' => $img->getError()
                ] : ['type' => gettype($img), 'value' => $img];
            }, $images)
        ]);
        
        $successfulUploads = 0;
        $attemptedUploads = 0;
        
        // Force MediaLibrary usage - NO LEGACY FALLBACK
        foreach ($images as $index => $image) {
            $attemptedUploads++;
            
            if ($image instanceof UploadedFile && $image->isValid()) {
                try {
                    \Log::info('ProductService: Processing individual image', [
                        'product_id' => $product->id,
                        'index' => $index,
                        'filename' => $image->getClientOriginalName()
                    ]);
                    
                    $product->addMedia($image)
                        ->usingFileName($image->getClientOriginalName())
                        ->withCustomProperties([
                            'type' => 'gallery', 
                            'order' => $index + 1,
                            'uploaded_at' => now()
                        ])
                        ->toMediaCollection('product-gallery', 'public');
                        
                    $successfulUploads++;
                    
                    // Forzar refresh del modelo para asegurar que los media se guarden
                    $product->refresh();
                    
                    \Log::info('ProductService: Successfully uploaded image', [
                        'product_id' => $product->id,
                        'index' => $index,
                        'filename' => $image->getClientOriginalName(),
                        'media_count_after' => $product->media()->count()
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('ProductService: Failed to upload individual image', [
                        'product_id' => $product->id,
                        'index' => $index,
                        'filename' => $image->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                \Log::warning('ProductService: Invalid image file', [
                    'product_id' => $product->id,
                    'index' => $index,
                    'is_uploaded_file' => $image instanceof UploadedFile,
                    'is_valid' => $image instanceof UploadedFile ? $image->isValid() : 'N/A',
                    'error' => $image instanceof UploadedFile ? $image->getError() : 'Not UploadedFile'
                ]);
            }
        }
        
        \Log::info('ProductService: MediaLibrary upload completed', [
            'product_id' => $product->id,
            'attempted_count' => $attemptedUploads,
            'successful_count' => $successfulUploads
        ]);
        
        if ($successfulUploads === 0 && $attemptedUploads > 0) {
            \Log::warning('ProductService: No images were successfully uploaded to MediaLibrary', [
                'product_id' => $product->id,
                'attempted_count' => $attemptedUploads,
                'successful_count' => $successfulUploads
            ]);
        }
    }

    private function deleteProductImages(Product $product): void
    {
        // Force MediaLibrary usage - Clear all media collections
        $product->clearMediaCollection('featured-image');
        $product->clearMediaCollection('product-gallery');
        
        // Clean up legacy fields if they exist (backward compatibility)
        if ($product->featured_image) {
            Storage::disk('public')->delete($product->featured_image);
            $product->featured_image = null;
        }

        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
            $product->images = null;
        }
        
        $product->save();
    }

    private function createProductVariants(Product $product, array $variants): void
    {
        foreach ($variants as $variantData) {
            $variant = new ProductVariant([
                'product_id' => $product->id,
                'name' => $variantData['name'],
                'sku' => $variantData['sku'] ?? Product::generateSku($product->name . ' ' . $variantData['name']),
                'price' => $variantData['price'],
                'stock' => $variantData['stock'] ?? 0,
                'weight' => $variantData['weight'] ?? null,
                'attributes' => $variantData['attributes'] ?? [],
                'status' => $variantData['status'] ?? 'active',
            ]);

            $variant->save();

            // Handle variant image upload
            if (isset($variantData['image']) && $variantData['image'] instanceof UploadedFile) {
                $path = $variantData['image']->store('products/'.$product->id.'/variants', 'public');
                $variant->image = $path;
                $variant->save();
            }
        }
    }

    private function updateProductVariants(Product $product, array $variants): void
    {
        // Get existing variant IDs
        $existingVariantIds = $product->variants()->pluck('id')->toArray();
        $updatedVariantIds = [];

        foreach ($variants as $variantData) {
            if (isset($variantData['id'])) {
                // Update existing variant
                $variant = ProductVariant::findOrFail($variantData['id']);
                $variant->update([
                    'name' => $variantData['name'] ?? $variant->name,
                    'price' => $variantData['price'] ?? $variant->price,
                    'stock' => $variantData['stock'] ?? $variant->stock,
                    'weight' => $variantData['weight'] ?? $variant->weight,
                    'attributes' => $variantData['attributes'] ?? $variant->attributes,
                    'status' => $variantData['status'] ?? $variant->status,
                ]);

                $updatedVariantIds[] = $variant->id;
            } else {
                // Create new variant
                $variant = new ProductVariant([
                    'product_id' => $product->id,
                    'name' => $variantData['name'],
                    'sku' => $variantData['sku'] ?? Product::generateSku($product->name . ' ' . $variantData['name']),
                    'price' => $variantData['price'],
                    'stock' => $variantData['stock'] ?? 0,
                    'weight' => $variantData['weight'] ?? null,
                    'attributes' => $variantData['attributes'] ?? [],
                    'status' => $variantData['status'] ?? 'active',
                ]);

                $variant->save();
                $updatedVariantIds[] = $variant->id;
            }
        }

        // Delete variants that weren't updated
        $variantsToDelete = array_diff($existingVariantIds, $updatedVariantIds);
        if (! empty($variantsToDelete)) {
            ProductVariant::whereIn('id', $variantsToDelete)->delete();
        }
    }
}
