<?php

namespace App\Modules\Vendor\Services;

use App\Models\User;
use App\Modules\Vendor\Vendor;
use App\Services\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorService
{
    private ImageProcessor $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function activateVendor(User $user, array $data): Vendor
    {
        // Handle image upload FIRST - outside transaction
        $tempImagePath = null;

        Log::info('=== VENDOR ACTIVATION: Starting ===', [
            'user_id' => $user->id,
            'has_store_image' => isset($data['store_image']),
            'store_image_type' => isset($data['store_image']) ? (is_object($data['store_image']) ? get_class($data['store_image']) : gettype($data['store_image'])) : null,
        ]);

        if (isset($data['store_image']) && ($data['store_image'] instanceof UploadedFile || is_string($data['store_image']))) {
            $tempImagePath = $this->storeVendorImage($data['store_image']);
            Log::info('VENDOR ACTIVATION: Image stored in temp', ['temp_path' => $tempImagePath]);
        }

        // Database transaction only for database operations
        $vendor = DB::transaction(function () use ($user, $data, $tempImagePath) {
            // Create vendor profile first (needed for ID)
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'store_name' => $data['store_name'],
                'store_slug' => $this->generateUniqueSlug($data['store_name']),
                'description' => $data['description'] ?? null,
                'store_image' => null, // Will be updated after moving image
                'status' => 'active',
            ]);

            Log::info('VENDOR ACTIVATION: Vendor created', [
                'vendor_id' => $vendor->id,
                'store_name' => $vendor->store_name,
            ]);

            // Update user role to vendor if not already set
            if ($user->role !== 'vendor') {
                $user->update(['role' => 'vendor']);
            }

            return $vendor;
        });

        // After successful transaction, move the image
        if ($tempImagePath) {
            try {
                $finalPath = $this->moveVendorImage($vendor, $tempImagePath);
                $vendor->update(['store_image' => $finalPath]);
                $vendor->refresh();

                Log::info('VENDOR ACTIVATION: Image moved to final location', [
                    'temp_path' => $tempImagePath,
                    'final_path' => $finalPath,
                ]);
            } catch (\Exception $e) {
                Log::error('VENDOR ACTIVATION: Failed to move image', [
                    'error' => $e->getMessage(),
                    'temp_path' => $tempImagePath,
                ]);
                if (Storage::disk('public')->exists($tempImagePath)) {
                    Storage::disk('public')->delete($tempImagePath);
                }
            }
        }

        return $vendor;
    }

    public function generateUniqueSlug(string $storeName): string
    {
        $slug = Str::slug($storeName);
        $originalSlug = $slug;
        $counter = 1;

        while (Vendor::where('store_slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function getVendorByUser(User $user): ?Vendor
    {
        return $user->vendor()->first();
    }

    public function canUserActivateVendor(User $user): bool
    {
        return $user->role === 'vendor' || $user->role === 'customer';
    }

    /**
     * Store vendor image using ImageProcessor.
     * Supports both UploadedFile and base64 encoded images.
     */
    public function storeVendorImage(UploadedFile|string|null $image): ?string
    {
        // Handle base64 encoded image (NativePHP)
        if (is_string($image) && str_starts_with($image, 'data:image')) {
            return $this->imageProcessor->processBase64Image($image);
        }
        
        // Handle regular file upload
        if (!$image instanceof UploadedFile) {
            Log::warning('VENDOR SERVICE: No valid image provided');
            return null;
        }

        // Check if file has content
        $fileSize = $image->getSize();
        if (!$fileSize || $fileSize === 0) {
            Log::error('VENDOR SERVICE: File is empty!');
            return null;
        }

        // Use ImageProcessor to process and save the image
        return $this->imageProcessor->processUploadedFile($image);
    }

    /**
     * Update vendor profile.
     */
    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        return DB::transaction(function () use ($vendor, $data) {
            // Handle image removal flag first
            if (isset($data['remove_image']) && $data['remove_image'] === true) {
                if ($vendor->store_image && Storage::disk('public')->exists($vendor->store_image)) {
                    Storage::disk('public')->delete($vendor->store_image);
                }
                $data['store_image'] = null;
            }
            elseif (array_key_exists('store_image', $data)) {
                $imageValue = $data['store_image'];
                
                // Case 1: Image removed (null)
                if ($imageValue === null) {
                    if ($vendor->store_image && Storage::disk('public')->exists($vendor->store_image)) {
                        Storage::disk('public')->delete($vendor->store_image);
                    }
                    $data['store_image'] = null;
                }
                // Case 2: New UploadedFile
                elseif ($imageValue instanceof UploadedFile) {
                    if ($vendor->store_image && Storage::disk('public')->exists($vendor->store_image)) {
                        Storage::disk('public')->delete($vendor->store_image);
                    }
                    $tempImagePath = $this->storeVendorImage($imageValue);
                    if ($tempImagePath) {
                        $data['store_image'] = $this->moveVendorImage($vendor, $tempImagePath);
                    } else {
                        unset($data['store_image']);
                    }
                }
                // Case 3: Base64 image (NativePHP)
                elseif (is_string($imageValue) && str_starts_with($imageValue, 'data:image')) {
                    if ($vendor->store_image && Storage::disk('public')->exists($vendor->store_image)) {
                        Storage::disk('public')->delete($vendor->store_image);
                    }
                    $tempImagePath = $this->storeVendorImage($imageValue);
                    if ($tempImagePath) {
                        $data['store_image'] = $this->moveVendorImage($vendor, $tempImagePath);
                    } else {
                        unset($data['store_image']);
                    }
                }
                // Case 4: Empty string - no change
                elseif (is_string($imageValue) && $imageValue === '') {
                    unset($data['store_image']);
                }
            } else {
                // Case 5: Key doesn't exist - keep existing
                unset($data['store_image']);
            }

            // Remove the remove_image flag from the data before updating
            unset($data['remove_image']);

            $vendor->update($data);
            return $vendor->fresh();
        });
    }

    /**
     * Move vendor image from temporary location to final vendor-specific path.
     */
    public function moveVendorImage(Vendor $vendor, string $tempImagePath): string
    {
        // Create vendor-specific directory path
        $vendorDir = 'vendors/' . $vendor->id;
        $filename = basename($tempImagePath);
        $finalPath = $vendorDir . '/' . $filename;
        
        // Ensure vendor directory exists
        Storage::disk('public')->makeDirectory($vendorDir);
        
        // Move file from temp location to final location
        if (Storage::disk('public')->exists($tempImagePath)) {
            Storage::disk('public')->move($tempImagePath, $finalPath);
        } else {
            throw new \Exception("Temporary image file not found: {$tempImagePath}");
        }
        
        return $finalPath;
    }

    /**
     * Delete vendor profile.
     */
    public function deleteVendor(Vendor $vendor): void
    {
        DB::transaction(function () use ($vendor) {
            if ($vendor->store_image && Storage::disk('public')->exists($vendor->store_image)) {
                Storage::disk('public')->delete($vendor->store_image);
            }

            $user = $vendor->user;
            $otherStoresCount = $user->vendors()->where('id', '!=', $vendor->id)->count();

            if ($otherStoresCount === 0) {
                $user->update(['role' => 'customer']);
            }

            $vendor->delete();
        });
    }
}
