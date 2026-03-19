<?php

namespace App\Console\Commands;

use App\Modules\Vendor\Product;
use App\Modules\Vendor\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignSeederProductsToCurrentUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:assign-to-user {--user-id= : Specific user ID to assign products to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassign seeder products to a specific user or current user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if (!$userId) {
            $this->error('Please provide --user-id=X option');
            return 1;
        }

        // Verify user exists
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }

        // Get user's vendor
        $vendor = DB::table('vendors')->where('user_id', $userId)->first();
        if (!$vendor) {
            $this->error("User {$userId} does not have a vendor profile");
            return 1;
        }

        $this->info("Found user: " . ($user->name ?? 'Unknown') . " (ID: {$userId})");
        $this->info("Found vendor: " . ($vendor->name ?? 'Unknown') . " (ID: {$vendor->id})");

        // Get products that need reassignment (created_by != userId but belong to this vendor)
        $productsToReassign = Product::where('vendor_id', $vendor->id)
            ->where('created_by', '!=', $userId)
            ->get();

        if ($productsToReassign->isEmpty()) {
            $this->info('✅ All products are already correctly assigned');
            return 0;
        }

        $this->info("Found {$productsToReassign->count()} products to reassign");

        // Reassign products
        $count = 0;
        foreach ($productsToReassign as $product) {
            $product->created_by = $userId;
            $product->save();
            $count++;
            
            $this->line("  ✓ Reassigned: {$product->name}");
        }

        $this->info("✅ Successfully reassigned {$count} products to user {$userId}");
        
        // Verify final count
        $finalCount = Product::where('vendor_id', $vendor->id)->count();
        $this->info("Vendor now has {$finalCount} total products");

        return 0;
    }
}
