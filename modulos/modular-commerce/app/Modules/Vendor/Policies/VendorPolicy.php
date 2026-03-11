<?php

namespace App\Modules\Vendor\Policies;

use App\Models\User;
use App\Modules\Vendor\Services\VendorService;
use App\Modules\Vendor\Vendor;

class VendorPolicy
{
    public function __construct(
        private VendorService $vendorService
    ) {}

    /**
     * Determine whether the user can activate vendor mode.
     */
    public function activateVendor(User $user): bool
    {
        return $this->vendorService->canUserActivateVendor($user);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vendor $vendor): bool
    {
        return (string) $user->id === (string) $vendor->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // Vendor creation is handled through activation
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vendor $vendor): bool
    {
        return (string) $user->id === (string) $vendor->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vendor $vendor): bool
    {
        return (string) $user->id === (string) $vendor->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vendor $vendor): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vendor $vendor): bool
    {
        return $user->role === 'admin';
    }
}
