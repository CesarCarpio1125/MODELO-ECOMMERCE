<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageHelper
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
     * Generate proper image URL for both web and Electron environments
     */
    public static function getImageUrl(?string $path, ?int $timestamp = null): ?string
    {
        if (!$path) {
            return null;
        }

        // Check if path is already a full URL to prevent double concatenation
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            // Path is already a full URL, just add timestamp if needed
            $url = $path;
            if ($timestamp) {
                $separator = str_contains($url, '?') ? '&' : '?';
                $url .= $separator . 't=' . $timestamp;
            }
            return $url;
        }

        // Check if we're in NativePHP/Electron environment
        if (self::isNativeEnvironment()) {
            // Use API route for NativePHP to bypass CSRF issues
            $baseUrl = self::getBaseUrl();
            $url = $baseUrl . '/api/images/' . ltrim($path, '/');
            
            // Add cache-busting parameter if timestamp provided
            if ($timestamp) {
                $url .= '?t=' . $timestamp;
            }
            
            return $url;
        }

        // For web environment, use regular asset URL with cache-busting
        $url = asset('storage/' . ltrim($path, '/'));
        
        // Add cache-busting parameter if timestamp provided
        if ($timestamp) {
            $url .= '?t=' . $timestamp;
        }
        
        return $url;
    }

    /**
     * Get the base URL based on current environment
     */
    public static function getBaseUrl(): string
    {
        $request = Request::capture();
        
        // If we're in a request context, use the current host
        if ($request && !empty($request->getHost())) {
            $scheme = $request->getScheme();
            $host = $request->getHttpHost();
            return $scheme . '://' . $host;
        }
        
        // Fallback: check if there's a running server port
        $host = env('APP_HOST', '127.0.0.1');
        $port = env('APP_PORT', env('NATIVEPHP_PORT', '8000'));
        
        // For local development
        if (app()->environment('local', 'native')) {
            // Try common dev ports - prioritize 8000 for consistency
            foreach ([8000, 8100, 3000] as $testPort) {
                $connection = @fsockopen($host, $testPort, $errno, $errstr, 1);
                if ($connection) {
                    fclose($connection);
                    $port = $testPort;
                    break;
                }
            }
        }
        
        return "http://{$host}:{$port}";
    }

    /**
     * Check if we're running in NativePHP/Electron environment
     */
    public static function isNativeEnvironment(): bool
    {
        // First check environment variable (most reliable for both CLI and HTTP)
        if (env('NATIVEPHP_RUNNING') === true || env('NATIVEPHP_RUNNING') === 'true' || env('NATIVEPHP_RUNNING') === '1') {
            return true;
        }
        
        // Check config environment
        if (config('app.env') === 'native') {
            return true;
        }
        
        // Check for NativePHP specific headers or indicators
        try {
            $request = Request::capture();
            
            // Check User-Agent for Electron
            $userAgent = $request->userAgent() ?? '';
            
            // Check for NativePHP specific headers
            $isNativePHP = $request->hasHeader('X-NativePHP') 
                || str_contains($userAgent, 'Electron')
                || str_contains($userAgent, 'NativePHP');
                
            if ($isNativePHP) {
                return true;
            }
        } catch (\Exception $e) {
            // If we can't capture request, fall back to other methods
        }
        
        return false;
    }

    /**
     * Get base URL for storage files
     */
    public static function getStorageBaseUrl(): string
    {
        if (self::isNativeEnvironment()) {
            // Use API route for NativePHP to bypass CSRF issues
            return self::getBaseUrl() . '/api/images';
        }

        return asset('storage');
    }

    /**
     * Convert file path to proper URL for current environment
     */
    public static function filePathToUrl(string $filePath): string
    {
        if (self::isNativeEnvironment()) {
            return self::getImageUrl($filePath);
        }

        return asset($filePath);
    }

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

            Log::info("ImageHelper: Serving image", [
                'requested_path' => $path,
                'full_path' => $fullPath,
                'exists' => file_exists($fullPath)
            ]);

            // Security: Check for path traversal attempts
            if (str_contains($path, '..') || str_starts_with($path, '/')) {
                Log::warning("ImageHelper: Suspicious path detected", [
                    'requested_path' => $path
                ]);
                abort(400, 'Invalid path');
            }

            // Check if file exists using both filesystem approaches
            if (!file_exists($fullPath) && !Storage::disk('public')->exists($path)) {
                Log::warning("ImageHelper: File not found - returning 404", [
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
                    Log::error("ImageHelper: Base path not found");
                    abort(500, 'Storage base path not found');
                }

                // Verify the file is within the allowed directory (security check)
                if ($realFullPath === false) {
                    // File exists but realpath returned false - return 404
                    Log::warning("ImageHelper: File exists but realpath returned false", [
                        'requested_path' => $path,
                        'full_path' => $fullPath
                    ]);
                    abort(404, 'Image not found: ' . $path);
                }

                if (!str_starts_with($realFullPath, $realBasePath)) {
                    Log::error("ImageHelper: Path traversal attempt detected", [
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
                    Log::warning("ImageHelper: Invalid file type", ['mime_type' => $mimeType]);
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

                Log::info("ImageHelper: Serving image successfully", [
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
            Log::error("ImageHelper: Error serving image", [
                'path' => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Error serving image: ' . $e->getMessage());
        }
    }

    /**
     * Store uploaded image properly
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

            Log::info("ImageHelper: Image stored successfully", [
                'directory' => $directory,
                'filename' => $filename,
                'extension_used' => $file->extension(),
                'path' => $path
            ]);

            return $path;

        } catch (\Exception $e) {
            Log::error("ImageHelper: Error storing image", [
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

            Log::info("ImageHelper: Image deletion", [
                'path' => $path,
                'deleted' => $deleted
            ]);

            return $deleted;

        } catch (\Exception $e) {
            Log::error("ImageHelper: Error deleting image", [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get default image URL
     */
    public static function getDefaultImageUrl(): string
    {
        return url('/images/default-store.jpg');
    }
}
