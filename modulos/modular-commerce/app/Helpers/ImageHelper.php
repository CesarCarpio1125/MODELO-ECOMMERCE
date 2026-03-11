<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

class ImageHelper
{
    /**
     * Generate proper image URL for both web and Electron environments
     */
    public static function getImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Check if we're in NativePHP/Electron environment
        if (self::isNativeEnvironment()) {
            // Use API route for NativePHP to bypass CSRF issues
            $baseUrl = self::getBaseUrl();
            return $baseUrl . '/api/images/' . ltrim($path, '/');
        }

        // For web environment, use regular asset URL
        return asset('storage/' . ltrim($path, '/'));
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
            return \App\Services\NativeImageService::getImageUrl($filePath);
        }

        return asset($filePath);
    }
}
