<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NativeEnvironment
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Detect if we're running in NativePHP/Electron
        $isNative = $this->isNativeEnvironment($request);
        
        if ($isNative) {
            // Set environment variable for this request
            putenv('NATIVEPHP_RUNNING=true');
            $_ENV['NATIVEPHP_RUNNING'] = true;
            $_SERVER['NATIVEPHP_RUNNING'] = true;
            
            // Configure app for native environment
            config(['app.asset_url' => null]); // Remove asset URL prefix
            config(['app.url' => 'http://localhost']); // Use localhost for URLs
        }
        
        return $next($request);
    }
    
    /**
     * Check if we're running in NativePHP/Electron environment
     */
    private function isNativeEnvironment(Request $request): bool
    {
        $userAgent = $request->userAgent() ?? '';
        
        // Check for Electron or NativePHP in user agent
        if (str_contains($userAgent, 'Electron') || str_contains($userAgent, 'NativePHP')) {
            return true;
        }
        
        // Check for NativePHP specific headers
        if ($request->hasHeader('X-NativePHP') || $request->hasHeader('X-Electron')) {
            return true;
        }
        
        // Check environment variable
        if (env('NATIVEPHP_RUNNING') === true) {
            return true;
        }
        
        return false;
    }
}
