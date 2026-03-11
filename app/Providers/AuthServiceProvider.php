<?php

namespace App\Providers;

use App\Models\Order;
use App\Modules\Vendor\Policies\ProductPolicy;
use App\Modules\Vendor\Policies\VendorPolicy;
use App\Modules\Vendor\Product;
use App\Modules\Vendor\Vendor;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Vendor::class => VendorPolicy::class,
        Product::class => ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register custom ability for vendor activation
        Gate::define('activate-vendor', function ($user) {
            return $user->canActivateVendor();
        });
    }
}
