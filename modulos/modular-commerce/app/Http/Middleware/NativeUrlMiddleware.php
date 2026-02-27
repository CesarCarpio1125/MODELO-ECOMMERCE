<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class NativeUrlMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if we're in Native/Electron environment
        if ($this->isNativeEnvironment($request)) {
            // Force proper URL configuration for Native
            $nativeUrl = 'http://127.0.0.1:8100';
            
            URL::forceRootUrl($nativeUrl);
            URL::forceScheme('http');
            
            // Update configuration at runtime
            config(['app.url' => $nativeUrl]);
        }
        
        return $next($request);
    }
    
    /**
     * Detect if we're in Native/Electron environment
     */
    private function isNativeEnvironment(Request $request): bool
    {
        // Check environment variable
        if (env('NATIVEPHP_RUNNING') === true || env('NATIVEPHP_RUNNING') === 'true') {
            return true;
        }
        
        // Check User-Agent for Electron
        $userAgent = $request->userAgent() ?? '';
        if (str_contains($userAgent, 'Electron') || str_contains($userAgent, 'NativePHP')) {
            return true;
        }
        
        // Check for NativePHP specific headers
        if ($request->hasHeader('X-NativePHP')) {
            return true;
        }
        
        // Check if request is coming from 127.0.0.1:8100
        $host = $request->getHost();
        $port = $request->getPort();
        
        if ($host === '127.0.0.1' && $port === 8100) {
            return true;
        }
        
        return false;
    }
}
