<?php

namespace App\Modules\Vendor\Services;

use App\Models\User;
use App\Modules\Vendor\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorService
{
    /**
     * Maximum image dimension for resizing
     */
    const MAX_IMAGE_SIZE = 1200;
    
    /**
     * JPEG quality for GD output
     */
    const JPEG_QUALITY = 85;
    
    /**
     * PNG compression level (0-9)
     */
    const PNG_COMPRESSION = 6;

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
     * Store vendor image using PHP GD for reliable processing.
     * Supports both UploadedFile and base64 encoded images.
     */
    public function storeVendorImage(UploadedFile|string|null $image): ?string
    {
        // Handle base64 encoded image (NativePHP)
        if (is_string($image) && str_starts_with($image, 'data:image')) {
            return $this->storeBase64ImageWithGD($image);
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

        // Use GD to process and save the image for reliability
        return $this->processUploadedFileWithGD($image);
    }
    
    /**
     * Process uploaded file using PHP GD library for reliability.
     */
    private function processUploadedFileWithGD(UploadedFile $file): ?string
    {
        try {
            // Get file info
            $tempPath = $file->getPathname();
            $originalExtension = $file->extension();
            
            Log::info('VENDOR SERVICE: Processing with GD', [
                'original_name' => $file->getClientOriginalName(),
                'extension' => $originalExtension,
                'size' => $file->getSize(),
            ]);
            
            // Create image resource from file using GD
            $imageResource = $this->createImageFromFile($tempPath, $originalExtension);
            
            if (!$imageResource) {
                Log::error('VENDOR SERVICE: Failed to create image resource');
                return null;
            }
            
            // Resize if needed using GD
            $resized = $this->resizeImageIfNeeded($imageResource, $tempPath, $originalExtension);
            
            // Generate unique filename with normalized extension
            $filename = uniqid() . '.jpg'; // Always use jpg for processed images
            $storagePath = 'vendors/temp/' . $filename;
            $fullPath = storage_path('app/public/' . $storagePath);
            
            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Save using GD (always as JPEG for consistency)
            $saved = $this->saveImageWithGD($imageResource, $fullPath, 'jpg');
            
            // Free memory
            imagedestroy($imageResource);
            
            if (!$saved) {
                Log::error('VENDOR SERVICE: Failed to save image with GD');
                return null;
            }
            
            Log::info('VENDOR SERVICE: Image stored with GD', [
                'path' => $storagePath,
                'filename' => $filename,
            ]);
            
            return $storagePath;
            
        } catch (\Exception $e) {
            Log::error('VENDOR SERVICE: GD processing error', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Create image resource from file path using GD.
     */
    private function createImageFromFile(string $filePath, string $extension): mixed
    {
        $extension = strtolower($extension);
        
        // Map extension to GD function
        $functionMap = [
            'jpg' => 'imagecreatefromjpeg',
            'jpeg' => 'imagecreatefromjpeg',
            'png' => 'imagecreatefrompng',
            'gif' => 'imagecreatefromgif',
            'webp' => 'imagecreatefromwebp',
        ];
        
        $function = $functionMap[$extension] ?? 'imagecreatefromjpeg';
        
        if (!function_exists($function)) {
            // Fallback to imagecreatefromstring
            $imageData = file_get_contents($filePath);
            return $imageData ? imagecreatefromstring($imageData) : null;
        }
        
        return $function($filePath);
    }
    
    /**
     * Resize image if it exceeds maximum dimensions.
     */
    private function resizeImageIfNeeded(mixed $imageResource, string $filePath, string $extension): bool
    {
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);
        
        // Check if resize is needed
        if ($width <= self::MAX_IMAGE_SIZE && $height <= self::MAX_IMAGE_SIZE) {
            return false;
        }
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min(self::MAX_IMAGE_SIZE / $width, self::MAX_IMAGE_SIZE / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        
        // Create new image with GD
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Handle transparency for PNG/GIF
        if ($extension === 'png' || $extension === 'gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparent);
        }
        
        // Resize
        imagecopyresampled(
            $newImage, $imageResource,
            0, 0, 0, 0,
            $newWidth, $newHeight, $width, $height
        );
        
        // Replace original
        imagedestroy($imageResource);
        
        // Save resized version
        $this->saveImageWithGD($newImage, $filePath, $extension);
        imagedestroy($newImage);
        
        return true;
    }
    
    /**
     * Save image using GD library.
     */
    private function saveImageWithGD(mixed $imageResource, string $path, string $format): bool
    {
        $format = strtolower($format);
        
        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        switch ($format) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($imageResource, pathinfo($path, PATHINFO_DIRNAME) . '/' . basename($path, '.' . pathinfo($path, PATHINFO_EXTENSION)) . '.jpg', self::JPEG_QUALITY);
            case 'png':
                return imagepng($imageResource, $path, self::PNG_COMPRESSION);
            case 'gif':
                return imagegif($imageResource, $path);
            case 'webp':
                return imagewebp($imageResource, $path, self::JPEG_QUALITY);
            default:
                return imagejpeg($imageResource, $path, self::JPEG_QUALITY);
        }
    }

    /**
     * Store base64 encoded image using GD for reliability.
     */
    private function storeBase64ImageWithGD(string $base64Data): ?string
    {
        try {
            // Extract base64 data from data URL
            if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64Data, $matches)) {
                Log::error('VENDOR SERVICE: Invalid base64 format');
                return null;
            }
            
            $extension = $matches[1];
            $imageData = base64_decode($matches[2]);
            
            if (!$imageData) {
                Log::error('VENDOR SERVICE: Failed to decode base64');
                return null;
            }
            
            // Normalize extension
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }
            
            // Create image resource from string using GD
            $imageResource = imagecreatefromstring($imageData);
            
            if (!$imageResource) {
                Log::error('VENDOR SERVICE: Failed to create image from base64');
                return null;
            }
            
            // Generate unique filename
            $filename = uniqid() . '.jpg'; // Save as jpg for consistency
            $path = 'vendors/temp/' . $filename;
            $fullPath = storage_path('app/public/' . $path);
            
            // Resize if needed
            $this->resizeImageIfNeeded($imageResource, $fullPath, 'jpg');
            
            // Save
            $saved = $this->saveImageWithGD($imageResource, $fullPath, 'jpg');
            imagedestroy($imageResource);
            
            if (!$saved) {
                Log::error('VENDOR SERVICE: Failed to save base64 image');
                return null;
            }
            
            Log::info('VENDOR SERVICE: Base64 image stored with GD', [
                'filename' => $filename,
                'size' => strlen($imageData),
            ]);
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error('VENDOR SERVICE: Base64 GD error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Move temporary image to permanent location for a vendor.
     */
    public function moveVendorImage(Vendor $vendor, string $tempPath): string
    {
        // Get actual extension from stored file
        $fullTempPath = storage_path('app/public/' . $tempPath);
        $actualExtension = pathinfo($fullTempPath, PATHINFO_EXTENSION);
        
        if (empty($actualExtension)) {
            $actualExtension = 'jpg'; // Default to jpg
        }
        
        $newPath = 'vendors/'.$vendor->id.'/store_image.' . $actualExtension;

        Log::info('VENDOR SERVICE: Moving image', [
            'vendor_id' => $vendor->id,
            'temp_path' => $tempPath,
            'new_path' => $newPath,
        ]);

        if (Storage::disk('public')->exists($tempPath)) {
            Storage::disk('public')->move($tempPath, $newPath);
        } else {
            Log::error('VENDOR SERVICE: Temp image not found!', ['temp_path' => $tempPath]);
        }

        return $newPath;
    }

    /**
     * Update vendor profile.
     */
    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        return DB::transaction(function () use ($vendor, $data) {
            if (array_key_exists('store_image', $data)) {
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

            $vendor->update($data);
            return $vendor->fresh();
        });
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
