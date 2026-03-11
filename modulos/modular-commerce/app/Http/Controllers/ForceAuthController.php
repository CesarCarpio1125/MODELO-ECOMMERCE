<?php

namespace App\Http\Controllers;

use App\Services\AuthSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForceAuthController extends Controller
{
    /**
     * Force logout and clear all session data
     */
    public function forceLogout(Request $request)
    {
        // Clear session using service
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json([
            'message' => 'Logged out successfully',
            'timestamp' => now()->toISOString(),
            'action' => 'Please login again with correct credentials'
        ]);
    }
    
    /**
     * Get current auth status with validation
     */
    public function authStatus(Request $request)
    {
        $validation = AuthSyncService::validateSession();
        $user = Auth::user();
        
        return response()->json([
            'authenticated' => Auth::check(),
            'validation' => $validation,
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
        ]);
    }
    
    /**
     * Fix authentication issues
     */
    public function fixAuth(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No user authenticated'
            ], 401);
        }
        
        // Refresh user session
        AuthSyncService::syncUserSession($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Authentication fixed',
            'user_id' => $user->id,
            'user_email' => $user->email,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
