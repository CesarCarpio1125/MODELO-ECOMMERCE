<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageProcessor
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

    /**
     * Process uploaded file using PHP GD library for reliability.
     */
    public function processUploadedFile(UploadedFile $file): ?string
    {
        try {
            // Get file info
            $tempPath = $file->getPathname();
            $originalExtension = $file->extension();
            
            Log::info('ImageProcessor: Processing with GD', [
                'original_name' => $file->getClientOriginalName(),
                'extension' => $originalExtension,
                'size' => $file->getSize(),
            ]);
            
            // Create image resource from file using GD
            $imageResource = $this->createImageFromFile($tempPath, $originalExtension);
            
            if (!$imageResource) {
                Log::error('ImageProcessor: Failed to create image resource');
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
                Log::error('ImageProcessor: Failed to save image with GD');
                return null;
            }
            
            Log::info('ImageProcessor: Image stored with GD', [
                'path' => $storagePath,
                'filename' => $filename,
            ]);
            
            return $storagePath;
            
        } catch (\Exception $e) {
            Log::error('ImageProcessor: GD processing error', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Process base64 encoded image using GD for reliability.
     */
    public function processBase64Image(string $base64Data): ?string
    {
        try {
            // Extract base64 data from data URL
            if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64Data, $matches)) {
                Log::error('ImageProcessor: Invalid base64 format');
                return null;
            }
            
            $extension = $matches[1];
            $imageData = base64_decode($matches[2]);
            
            if (!$imageData) {
                Log::error('ImageProcessor: Failed to decode base64');
                return null;
            }
            
            // Normalize extension
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }
            
            // Create image resource from string using GD
            $imageResource = imagecreatefromstring($imageData);
            
            if (!$imageResource) {
                Log::error('ImageProcessor: Failed to create image from base64');
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
                Log::error('ImageProcessor: Failed to save base64 image');
                return null;
            }
            
            Log::info('ImageProcessor: Base64 image stored with GD', [
                'filename' => $filename,
                'size' => strlen($imageData),
            ]);
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error('ImageProcessor: Base64 GD error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Move temporary image to permanent location for a vendor.
     */
    public function moveVendorImage(string $vendorId, string $tempPath): string
    {
        // Get actual extension from stored file
        $fullTempPath = storage_path('app/public/' . $tempPath);
        $actualExtension = pathinfo($fullTempPath, PATHINFO_EXTENSION);
        
        if (empty($actualExtension)) {
            $actualExtension = 'jpg'; // Default to jpg
        }
        
        // Generate unique filename with timestamp to avoid caching
        $timestamp = time();
        $uniqueId = uniqid();
        $filename = "store_image_{$timestamp}_{$uniqueId}.{$actualExtension}";
        $newPath = 'vendors/'.$vendorId.'/'.$filename;

        Log::info('ImageProcessor: Moving image', [
            'vendor_id' => $vendorId,
            'temp_path' => $tempPath,
            'new_path' => $newPath,
            'filename' => $filename,
        ]);

        if (Storage::disk('public')->exists($tempPath)) {
            Storage::disk('public')->move($tempPath, $newPath);
        } else {
            Log::error('ImageProcessor: Temp image not found!', ['temp_path' => $tempPath]);
        }

        return $newPath;
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
}
