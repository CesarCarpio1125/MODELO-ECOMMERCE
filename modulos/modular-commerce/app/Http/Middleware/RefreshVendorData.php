<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RefreshVendorData
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only for vendor dashboard
        if ($request->route()->getName() === 'vendor.dashboard') {
            $user = Auth::user();
            
            // Force fresh vendor data
            $vendors = $user->vendors()->get();
            
            // Add fresh data to Inertia props
            if ($response instanceof \Inertia\Response) {
                $response->with('vendors', $vendors);
            }
        }
        
        return $response;
    }
}
