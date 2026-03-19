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
        // Log the data being passed for debugging
        \Log::info('Creating product with data', [
            'vendor_id' => $vendor->id,
            'vendor_id_type' => gettype($vendor->id),
            'data' => $data,
            'data_types' => array_map('gettype', $data)
        ]);

        $product = new Product([
            'vendor_id' => $vendor->id, // ULID string - no casting
            'name' => $data['name'],
            'slug' => Product::generateSlug($data['name']),
            'description' => $data['description'] ?? null,
            'price' => (float) $data['price'],
            'stock_quantity' => (int) ($data['stock_quantity'] ?? $data['stock'] ?? 0),
            'sku' => $data['sku'] ?? Product::generateSku($data['name']),
            'weight' => $data['weight'] ?? null,
            'dimensions' => !empty($data['dimensions']) ? json_encode($data['dimensions']) : null,
            'status' => $data['status'] ?? 'draft',
            'category_id' => $data['category_id'] ?? null,
            'tags' => !empty($data['tags']) ? json_encode($data['tags']) : null,
            'created_by' => auth()->id(),
        ]);

        \Log::info('Product model attributes before save', [
            'attributes' => $product->getAttributes(),
            'dirty' => $product->getDirty()
        ]);

        $product->save();

        // Handle featured image upload with MediaLibrary
        if (isset($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            $this->uploadFeaturedImage($product, $data['featured_image']);
        }

        // Handle multiple image uploads with MediaLibrary
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
        \Log::info('ProductService: updateProduct called', [
            'product_id' => $product->id,
            'data_keys' => array_keys($data),
            'has_featured_image' => isset($data['featured_image']),
            'featured_image_type' => isset($data['featured_image']) ? gettype($data['featured_image']) : 'not_set',
            'has_images' => isset($data['images']),
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
            \Log::info('ProductService: Calling uploadFeaturedImage from updateProduct');
            $this->uploadFeaturedImage($product, $data['featured_image']);
        }

        // Handle multiple image uploads
        if (isset($data['images']) && is_array($data['images'])) {
            \Log::info('ProductService: Calling uploadProductImages from updateProduct');
            $this->uploadProductImages($product, $data['images']);
        }

        // Update variants if provided
        if (isset($data['variants']) && is_array($data['variants'])) {
            $this->updateProductVariants($product, $data['variants']);
        }

        \Log::info('ProductService: updateProduct completed', [
            'product_id' => $product->id,
            'featured_image_after' => $product->featured_image,
            'images_after' => $product->images
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

        return $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($product) {
                $product->featured_image_url = $product->getFeaturedImageUrl();
                return $product;
            });
    }

    private function uploadFeaturedImage(Product $product, UploadedFile $image): void
    {
        \Log::info('ProductService: Starting featured image upload with MediaLibrary', [
            'product_id' => $product->id,
            'original_name' => $image->getClientOriginalName(),
            'size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
        ]);

        try {
            $product->addMediaFromRequest('featured_image')
                ->usingFileName($product->slug . '-featured.' . $image->getClientOriginalExtension())
                ->withCustomProperties([
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'uploaded_by' => auth()->id(),
                ])
                ->toMediaCollection('featured-image', 'public');

            \Log::info('ProductService: Featured image uploaded successfully to MediaLibrary', [
                'product_id' => $product->id,
                'featured_image_url' => $product->getFeaturedImageUrl()
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductService: Failed to upload featured image to MediaLibrary', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback al sistema antiguo
            $this->uploadFeaturedImageLegacy($product, $image);
        }
    }

    private function uploadFeaturedImageLegacy(Product $product, UploadedFile $image): void
    {
        \Log::info('ProductService: Using legacy featured image upload', [
            'product_id' => $product->id,
            'original_name' => $image->getClientOriginalName(),
            'size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
            'upload_path' => $product->getFeaturedImagePath()
        ]);

        try {
            $path = $image->store($product->getFeaturedImagePath(), 'public');
            
            \Log::info('ProductService: Image stored successfully', [
                'product_id' => $product->id,
                'stored_path' => $path,
                'full_storage_path' => storage_path('app/public/' . $path),
                'file_exists_after_store' => file_exists(storage_path('app/public/' . $path))
            ]);

            $product->featured_image = $path;
            $product->save();

            \Log::info('ProductService: Product updated with featured image', [
                'product_id' => $product->id,
                'featured_image_field' => $product->featured_image,
                'featured_image_url' => $product->getFeaturedImageUrl()
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductService: Failed to upload featured image', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function uploadProductImages(Product $product, array $images): void
    {
        \Log::info('ProductService: Starting multiple images upload with MediaLibrary', [
            'product_id' => $product->id,
            'images_count' => count($images),
            'images_data' => array_map(fn($img) => $img instanceof UploadedFile ? [
                'original_name' => $img->getClientOriginalName(),
                'size' => $img->getSize(),
                'mime_type' => $img->getMimeType()
            ] : 'not_uploaded', $images)
        ]);

        $uploadedCount = 0;

        foreach ($images as $key => $image) {
            if ($image instanceof UploadedFile) {
                try {
                    $product->addMedia($image)
                        ->usingFileName($product->slug . '-gallery-' . ($key + 1) . '.' . $image->getClientOriginalExtension())
                        ->withCustomProperties([
                            'product_id' => $product->id,
                            'vendor_id' => $product->vendor_id,
                            'uploaded_by' => auth()->id(),
                            'gallery_order' => $key,
                        ])
                        ->toMediaCollection('product-gallery', 'public');

                    $uploadedCount++;
                    
                    \Log::info('ProductService: Image uploaded successfully to MediaLibrary', [
                        'product_id' => $product->id,
                        'image_key' => $key,
                        'original_name' => $image->getClientOriginalName()
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('ProductService: Failed to upload image to MediaLibrary', [
                        'product_id' => $product->id,
                        'image_key' => $key,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        if ($uploadedCount > 0) {
            \Log::info('ProductService: Product images uploaded successfully to MediaLibrary', [
                'product_id' => $product->id,
                'uploaded_count' => $uploadedCount,
                'total_images_after' => count($product->getCarouselImages())
            ]);
        } else {
            \Log::warning('ProductService: No images were successfully uploaded to MediaLibrary', [
                'product_id' => $product->id,
                'attempted_count' => count($images),
                'successful_count' => $uploadedCount
            ]);
            
            // Fallback al sistema antiguo
            $this->uploadProductImagesLegacy($product, $images);
        }
    }

    private function uploadProductImagesLegacy(Product $product, array $images): void
    {
        \Log::info('ProductService: Using legacy multiple images upload', [
            'product_id' => $product->id,
            'images_count' => count($images),
            'images_data' => array_map(fn($img) => $img instanceof UploadedFile ? [
                'original_name' => $img->getClientOriginalName(),
                'size' => $img->getSize(),
                'mime_type' => $img->getMimeType()
            ] : 'not_uploaded', $images)
        ]);

        $uploadedImages = [];
        $currentImages = $product->images ?? [];

        foreach ($images as $key => $image) {
            if ($image instanceof UploadedFile) {
                try {
                    $path = $image->store($product->getImagesPath(), 'public');
                    
                    \Log::info('ProductService: Image stored successfully', [
                        'product_id' => $product->id,
                        'image_key' => $key,
                        'stored_path' => $path,
                        'full_storage_path' => storage_path('app/public/' . $path),
                        'file_exists_after_store' => file_exists(storage_path('app/public/' . $path))
                    ]);
                    
                    $uploadedImages[] = $path;
                } catch (\Exception $e) {
                    \Log::error('ProductService: Failed to upload image', [
                        'product_id' => $product->id,
                        'image_key' => $key,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        if (! empty($uploadedImages)) {
            $allImages = array_merge($currentImages, $uploadedImages);
            
            \Log::info('ProductService: Updating product with all images', [
                'product_id' => $product->id,
                'current_images_count' => count($currentImages),
                'new_images_count' => count($uploadedImages),
                'total_images_after_merge' => count($allImages),
                'images_field' => $allImages
            ]);

            $product->images = $allImages;
            $product->save();

            \Log::info('ProductService: Product images updated successfully', [
                'product_id' => $product->id,
                'final_images_count' => count($product->images)
            ]);
        } else {
            \Log::warning('ProductService: No images were successfully uploaded', [
                'product_id' => $product->id,
                'attempted_count' => count($images),
                'successful_count' => count($uploadedImages)
            ]);
        }
    }

    private function deleteProductImages(Product $product): void
    {
        // Eliminar imágenes de MediaLibrary
        $product->clearMediaCollection('featured-image');
        $product->clearMediaCollection('product-gallery');

        // Eliminar imágenes del sistema antiguo (compatibilidad)
        if ($product->featured_image) {
            Storage::disk('public')->delete($product->featured_image);
        }

        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }
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
