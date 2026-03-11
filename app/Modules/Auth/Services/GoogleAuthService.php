<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    public function findOrCreateUser(SocialiteUser $googleUser): User
    {
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Update user info if needed
            $user->update([
                'name' => $googleUser->getName(),
                // Add avatar or other fields if desired
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(16)),
                'email_verified_at' => now(),
            ]);
        }

        return $user;
    }

    public function redirectToGoogle()
    {
        // This could be here if needed, but since Socialite facade is used in controller
        // Perhaps just a placeholder or move logic here
    }

    public function handleCallback(string $provider)
    {
        // Handle callback, but since in controller, perhaps not needed
    }
}
