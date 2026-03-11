<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        try {
            // Safely load user with vendors relationship
            $userWithVendors = $user ? $user->load('vendors') : null;
        } catch (\Exception $e) {
            // Log error and continue without vendors
            \Log::error('Error loading vendors in HandleInertiaRequests', [
                'error' => $e->getMessage(),
                'user_id' => $user?->id,
            ]);
            $userWithVendors = $user;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userWithVendors,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
