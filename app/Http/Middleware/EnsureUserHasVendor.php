<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasVendor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if user has a vendor profile - fix para error de contexto
        try {
            $hasVendor = $user->vendors()->exists();
        } catch (\Exception $e) {
            // Si hay error, asumimos que no tiene vendor
            $hasVendor = false;
        }
        
        if (!$hasVendor) {
            return redirect()->route('vendor.activate')
                ->with('info', 'Please activate your vendor profile first.');
        }

        return $next($request);
    }
}
