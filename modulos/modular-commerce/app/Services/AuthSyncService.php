<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthSyncService
{
    /**
     * Force user session refresh and synchronization
     */
    public static function syncUserSession(User $user): void
    {
        // Clear existing session
        Session::flush();
        
        // Re-authenticate with fresh user data
        Auth::login($user);
        
        // Regenerate session ID
        Session::regenerate();
    }
    
    /**
     * Get current authenticated user with fresh data
     */
    public static function getCurrentUser(): ?User
    {
        if (!Auth::check()) {
            return null;
        }
        
        return Auth::user()->fresh();
    }
    
    /**
     * Validate user session consistency
     */
    public static function validateSession(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [
                'valid' => false,
                'reason' => 'No authenticated user',
                'user_id' => null,
            ];
        }
        
        // Check if user still exists in database
        $freshUser = User::find($user->id);
        if (!$freshUser) {
            return [
                'valid' => false,
                'reason' => 'User no longer exists in database',
                'user_id' => $user->id,
            ];
        }
        
        return [
            'valid' => true,
            'reason' => 'Session valid',
            'user_id' => $user->id,
            'user_email' => $freshUser->email,
            'user_role' => $freshUser->role,
            'vendors_count' => $freshUser->vendors()->count(),
        ];
    }
}
