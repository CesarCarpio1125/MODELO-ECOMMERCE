<?php

namespace App\Modules\Vendor\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Requests\ActivateVendorRequest;
use App\Modules\Vendor\Requests\UpdateVendorRequest;
use App\Modules\Vendor\Services\VendorService;
use App\Modules\Vendor\Vendor;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function __construct(
        private VendorService $vendorService
    ) {}

    /**
     * Show vendor activation form.
     */
    public function showActivateForm(): \Inertia\Response
    {
        $user = auth()->user();

        // Check if user can activate vendor mode
        if (! $this->vendorService->canUserActivateVendor($user)) {
            return redirect()
                ->route('vendor.dashboard')
                ->with('info', 'You already have vendor access.');
        }

        return Inertia::render('Vendor/Activate', [
            'user' => $user,
        ]);
    }

    /**
     * Activate vendor mode for authenticated user.
     */
    public function activate(ActivateVendorRequest $request)
    {
        // AGGRESSIVE LOGGING - Debug NativePHP file upload issues
        \Illuminate\Support\Facades\Log::emergency('=== VENDOR ACTIVATION: START ===', [
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email ?? 'unknown',
            'request_method' => request()->method(),
            'request_url' => request()->fullUrl(),
            'all_request_data' => request()->all(),
            'files_keys' => array_keys(request()->allFiles()),
            'has_store_image' => request()->hasFile('store_image'),
            'store_image_object' => request()->file('store_image') ? get_class(request()->file('store_image')) : 'NULL',
            'store_image_details' => request()->file('store_image') ? [
                'original_name' => request()->file('store_image')->getClientOriginalName(),
                'mime_type' => request()->file('store_image')->getMimeType(),
                'size' => request()->file('store_image')->getSize(),
                'error' => request()->file('store_image')->getError(),
                'is_valid' => request()->file('store_image')->isValid(),
            ] : 'NO_FILE',
        ]);

        try {
            $user = auth()->user();
            $validatedData = $request->validated();
            
            // Explicitly get the file from the request - validated() may not include files properly
            $file = $request->file('store_image');
            
            // Log what we receive from the request
            \Illuminate\Support\Facades\Log::info('VENDOR CONTROLLER: Received activation request', [
                'user_id' => $user->id,
                'has_store_image_in_validated' => isset($validatedData['store_image']),
                'has_store_image_in_request' => $request->hasFile('store_image'),
                'file_object' => $file ? get_class($file) : null,
                'store_name' => $validatedData['store_name'] ?? null,
                'files' => $request->files->keys(),
                'all_data_keys' => array_keys($validatedData),
            ]);
            
            // If file is not in validated data but exists in request, add it manually
            if (empty($validatedData['store_image']) && $file) {
                $validatedData['store_image'] = $file;
                \Illuminate\Support\Facades\Log::info('VENDOR CONTROLLER: Manually added file to data', [
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
            
            $vendor = $this->vendorService->activateVendor($user, $validatedData);

            // Refresh user to get updated role
            $user->refresh();

            // Force session refresh to update authenticated user data
            Session::regenerate();
            auth()->login($user);

            return redirect()
                ->route('vendor.dashboard')
                ->with('success', 'Vendor profile activated successfully!')
                ->with('vendor_activated', true);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('VENDOR CONTROLLER: Activation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withErrors(['activate_vendor' => $e->getMessage()]);
        }
    }

    /**
     * Show vendor dashboard with all user stores.
     */
    public function dashboard(): \Inertia\Response
    {
        $user = auth()->user();
        $vendors = $user->vendors()->get(); // Get all stores for this user

        return Inertia::render('Vendor/Dashboard', [
            'vendors' => $vendors,
            'user' => $user,
        ]);
    }

    /**
     * Check if user can activate vendor mode.
     */
    public function canActivate(): JsonResponse
    {
        $user = auth()->user();
        $canActivate = $this->vendorService->canUserActivateVendor($user);

        return response()->json([
            'can_activate' => $canActivate,
        ]);
    }

    /**
     * Update vendor profile.
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $this->authorize('update', $vendor);

        try {
            $validatedData = $request->validated();
            
            // Explicitly get the file from the request - validated() may not include files properly
            $file = $request->file('store_image');
            
            // Log what we receive from the request
            \Illuminate\Support\Facades\Log::info('VENDOR CONTROLLER: Received update request', [
                'vendor_id' => $vendor->id,
                'has_store_image_in_validated' => isset($validatedData['store_image']),
                'has_store_image_in_request' => $request->hasFile('store_image'),
                'file_object' => $file ? get_class($file) : null,
                'store_name' => $validatedData['store_name'] ?? null,
                'files' => $request->files->keys(),
                'all_data_keys' => array_keys($validatedData),
            ]);
            
            // If file is not in validated data but exists in request, add it manually
            if (empty($validatedData['store_image']) && $file) {
                $validatedData['store_image'] = $file;
                \Illuminate\Support\Facades\Log::info('VENDOR CONTROLLER: Manually added file to data', [
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
            
            $updatedVendor = $this->vendorService->updateVendor($vendor, $validatedData);

            return redirect()
                ->route('vendor.dashboard')
                ->with('success', 'Store updated successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('VENDOR CONTROLLER: Update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withErrors(['update_vendor' => $e->getMessage()]);
        }
    }

    /**
     * Delete vendor profile.
     */
    public function destroy(Vendor $vendor)
    {
        $this->authorize('delete', $vendor);

        try {
            $this->vendorService->deleteVendor($vendor);

            return redirect()
                ->route('vendor.dashboard')
                ->with('success', 'Store deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['delete_vendor' => $e->getMessage()]);
        }
    }

    /**
     * Show edit form for specific vendor.
     */
    public function edit(): \Inertia\Response
    {
        $user = auth()->user();
        $vendorId = request('vendor'); // Get vendor ID from query parameter

        if (! $vendorId) {
            // If no vendor ID provided, get the first vendor
            $vendor = $user->vendors()->first();
        } else {
            $vendor = Vendor::findOrFail($vendorId);

            // Verify ownership - cast to string to handle ULID type comparison
            if ((string) $vendor->user_id !== (string) $user->id) {
                abort(403, 'Unauthorized');
            }
        }

        return Inertia::render('Vendor/Edit', [
            'vendor' => $vendor,
            'user' => $user,
        ]);
    }
}
