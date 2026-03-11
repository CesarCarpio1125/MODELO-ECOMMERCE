<?php

namespace App\Http\Controllers;

use App\Modules\Vendor\Vendor;
use App\Modules\Vendor\Product;
use App\Models\Order;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SyncController extends Controller
{
    /**
     * Get all data for current user (sync endpoint)
     */
    public function sync(Request $request)
    {
        $user = Auth::user();
        $lastSync = $request->input('last_sync');
        
        $data = [
            'user' => $user,
            'timestamp' => now()->toISOString(),
            'vendors' => [],
            'products' => [],
            'orders' => [],
            'activities' => [],
        ];
        
        // Get ALL vendors for this user (not just one)
        $vendorsQuery = Vendor::where('user_id', $user->id);
        
        if ($lastSync) {
            $vendorsQuery->where('updated_at', '>', $lastSync);
        }
        
        $vendors = $vendorsQuery->get();
        $data['vendors'] = $vendors;
        
        // Get products for all vendors
        if ($vendors->isNotEmpty()) {
            $productsQuery = Product::whereIn('vendor_id', $vendors->pluck('id'));
            if ($lastSync) {
                $productsQuery->where('updated_at', '>', $lastSync);
            }
            $data['products'] = $productsQuery->get();
        }
        
        // Get user orders
        $ordersQuery = Order::where('user_id', $user->id);
        if ($lastSync) {
            $ordersQuery->where('updated_at', '>', $lastSync);
        }
        $data['orders'] = $ordersQuery->with('items.product')->get();
        
        // Get recent activities
        $activitiesQuery = Activity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50);
        
        if ($lastSync) {
            $activitiesQuery->where('created_at', '>', $lastSync);
        }
        $data['activities'] = $activitiesQuery->get();
        
        return response()->json($data);
    }
    
    /**
     * Force refresh all cached data
     */
    public function refresh(Request $request)
    {
        $user = Auth::user();
        
        // Clear user-specific caches
        $cacheKeys = [
            "vendor_data_{$user->id}",
            "user_orders_{$user->id}",
            "user_activities_{$user->id}",
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        
        // Clear image cache for NativePHP
        if (app(\App\Helpers\ImageHelper::class)->isNativeEnvironment()) {
            // This will trigger image refresh on next load
            Cache::forget('native_image_cache');
        }
        
        return response()->json([
            'message' => 'Cache cleared successfully',
            'timestamp' => now()->toISOString(),
            'cleared_keys' => $cacheKeys,
        ]);
    }
    
    /**
     * Get system status for debugging
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        
        return response()->json([
            'environment' => app()->environment(),
            'is_native' => \App\Helpers\ImageHelper::isNativeEnvironment(),
            'user_agent' => $request->userAgent(),
            'current_url' => $request->fullUrl(),
            'base_url' => \App\Helpers\ImageHelper::getBaseUrl(),
            'storage_url' => \App\Helpers\ImageHelper::getStorageBaseUrl(),
            'cache_status' => [
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
            ],
            'auth_status' => [
                'authenticated' => Auth::check(),
                'user_id' => $user ? $user->id : null,
                'user_email' => $user ? $user->email : null,
                'user_name' => $user ? $user->name : null,
                'user_role' => $user ? $user->role : null,
                'vendors_count' => $user ? $user->vendors()->count() : 0,
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
