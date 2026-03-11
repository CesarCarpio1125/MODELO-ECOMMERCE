<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NativeImageService
{
    /**
     * Serve image file for Native environment
     * This method serves files from storage/app/public
     */
    public static function serveImage(string $path)
    {
        // Force log to be written immediately
        Log::setDefaultDriver('stack');

        try {
            // Normalize path - remove any leading slashes or storage/ prefix
            $path = ltrim(str_replace('storage/', '', $path), '/');
            $fullPath = storage_path('app/public/' . $path);

            // DEBUG: Write to a separate debug file
            file_put_contents(
                storage_path('debug_image.log'),
                "[" . date('Y-m-d H:i:s') . "] serveImage called with path: $path, fullPath: $fullPath, exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n",
                FILE_APPEND
            );

            Log::info("NativeImageService: Serving image", [
                'requested_path' => $path,
                'full_path' => $fullPath,
                'exists' => file_exists($fullPath)
            ]);

            // Security: Check for path traversal attempts
            if (str_contains($path, '..') || str_starts_with($path, '/')) {
                Log::warning("NativeImageService: Suspicious path detected", [
                    'requested_path' => $path
                ]);
                abort(400, 'Invalid path');
            }

            // Check if file exists using both filesystem approaches
            if (!file_exists($fullPath) && !Storage::disk('public')->exists($path)) {
                Log::warning("NativeImageService: File not found - returning 404", [
                    'requested_path' => $path,
                    'full_path' => $fullPath
                ]);
                abort(404, 'Image not found');
            }

            // Try direct file serving first
            if (file_exists($fullPath)) {
                // Security check - prevent directory traversal
                $realBasePath = realpath(storage_path('app/public'));
                $realFullPath = realpath($fullPath);

                if (!$realBasePath) {
                    Log::error("NativeImageService: Base path not found");
                    abort(500, 'Storage base path not found');
                }

                // Verify the file is within the allowed directory (security check)
                if ($realFullPath === false) {
                    // File exists but realpath returned false - return 404
                    Log::warning("NativeImageService: File exists but realpath returned false", [
                        'requested_path' => $path,
                        'full_path' => $fullPath
                    ]);
                    abort(404, 'Image not found: ' . $path);
                }

                if (!str_starts_with($realFullPath, $realBasePath)) {
                    Log::error("NativeImageService: Path traversal attempt detected", [
                        'requested_path' => $path,
                        'resolved_path' => $realFullPath,
                        'base_path' => $realBasePath
                    ]);
                    abort(403, 'Access denied - path traversal detected');
                }

                // Check if it's actually an image file
                $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $mimeType = mime_content_type($realFullPath);

                if (!in_array($mimeType, $allowedMimes)) {
                    Log::warning("NativeImageService: Invalid file type", ['mime_type' => $mimeType]);
                    abort(400, 'Invalid file type - not an image');
                }

                // Generate proper headers for Native
                $headers = [
                    'Content-Type' => $mimeType,
                    'Cache-Control' => 'public, max-age=31536000',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET',
                    'Access-Control-Allow-Headers' => 'Content-Type',
                ];

                Log::info("NativeImageService: Serving image successfully", [
                    'path' => $path,
                    'mime_type' => $mimeType,
                    'size' => filesize($fullPath)
                ]);

                return response()->file($fullPath, $headers);
            }

            // Fallback to Laravel Storage
            return Storage::disk('public')->response($path);

        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            // Re-throw HTTP exceptions (like abort(404), abort(403), etc.) immediately
            throw $e;
        } catch (\Exception $e) {
            Log::error("NativeImageService: Error serving image", [
                'path' => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Error serving image: ' . $e->getMessage());
        }
    }

    /**
     * Get proper image URL for Native environment
     */
    public static function getImageUrl(string $path): string
    {
        if (empty($path)) {
            return self::getDefaultImageUrl();
        }

        // Remove 'storage/' prefix if present
        $cleanPath = str_replace('storage/', '', $path);

        // Check if file exists
        $fullPath = storage_path('app/public/' . $cleanPath);
        if (!file_exists($fullPath)) {
            Log::warning("NativeImageService: Image file not found", [
                'path' => $cleanPath,
                'full_path' => $fullPath
            ]);
            return self::getDefaultImageUrl();
        }

        // Use API route for NativePHP to bypass CSRF issues
        $baseUrl = \App\Helpers\ImageHelper::getBaseUrl();
        return $baseUrl . '/api/images/' . ltrim($cleanPath, '/');
    }

    /**
     * Get default image URL
     */
    public static function getDefaultImageUrl(): string
    {
        return url('/images/default-store.jpg');
    }

    /**
     * Store uploaded image properly for Native
     * Uses extension() method which is more reliable than getClientOriginalExtension()
     * as it detects the real extension based on MIME type.
     */
    public static function storeImage($file, string $directory = 'vendors'): ?string
    {
        try {
            if (!$file) {
                return null;
            }

            // Generate unique filename - use extension() for reliable extension detection
            $filename = Str::random(40) . '.' . $file->extension();
            $path = $file->storeAs($directory, $filename, 'public');

            Log::info("NativeImageService: Image stored successfully", [
                'directory' => $directory,
                'filename' => $filename,
                'extension_used' => $file->extension(),
                'path' => $path
            ]);

            return $path;

        } catch (\Exception $e) {
            Log::error("NativeImageService: Error storing image", [
                'error' => $e->getMessage(),
                'directory' => $directory
            ]);
            return null;
        }
    }

    /**
     * Delete image from storage
     */
    public static function deleteImage(?string $path): bool
    {
        if (empty($path)) {
            return true;
        }

        try {
            $deleted = Storage::disk('public')->delete($path);

            Log::info("NativeImageService: Image deletion", [
                'path' => $path,
                'deleted' => $deleted
            ]);

            return $deleted;

        } catch (\Exception $e) {
            Log::error("NativeImageService: Error deleting image", [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

