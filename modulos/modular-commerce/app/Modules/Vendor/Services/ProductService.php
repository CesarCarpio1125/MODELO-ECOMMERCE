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
            $this->uploadFeaturedImage($product, $data['featured_image']);
        }

        // Handle multiple image uploads
        if (isset($data['images']) && is_array($data['images'])) {
            $this->uploadProductImages($product, $data['images']);
        }

        // Update variants if provided
        if (isset($data['variants']) && is_array($data['variants'])) {
            $this->updateProductVariants($product, $data['variants']);
        }

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
        $path = $image->store($product->getFeaturedImagePath(), 'public');
        $product->featured_image = $path;
        $product->save();
    }

    private function uploadProductImages(Product $product, array $images): void
    {
        $uploadedImages = [];

        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $path = $image->store($product->getImagesPath(), 'public');
                $uploadedImages[] = $path;
            }
        }

        if (! empty($uploadedImages)) {
            $currentImages = $product->images ?? [];
            $product->images = array_merge($currentImages, $uploadedImages);
            $product->save();
        }
    }

    private function deleteProductImages(Product $product): void
    {
        // Delete featured image
        if ($product->featured_image) {
            Storage::disk('public')->delete($product->featured_image);
        }

        // Delete product images
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
