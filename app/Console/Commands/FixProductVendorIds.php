<?php

namespace App\Console\Commands;

use App\Modules\Vendor\Product;
use App\Modules\Vendor\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixProductVendorIds extends Command
{
    protected $signature = 'app:fix-product-vendor-ids';
    protected $description = 'Fix corrupt vendor_id data in products (ULID string vs integer issue)';

    public function handle()
    {
        $this->info('🔧 Fixing corrupt vendor_id data in products...');
        
        $fixedCount = 0;
        $errorCount = 0;
        
        // Get all products with potentially corrupt vendor_id
        $products = Product::all();
        
        $this->info("Found {$products->count()} products to check...");
        
        foreach ($products as $product) {
            try {
                // Check if vendor_id is corrupt (integer or empty)
                if (is_numeric($product->vendor_id) || empty($product->vendor_id)) {
                    $this->warn("Product {$product->id} has corrupt vendor_id: {$product->vendor_id}");
                    
                    // Try to find the correct vendor by looking at created_by user
                    $user = \App\Models\User::find($product->created_by);
                    
                    if ($user && $user->vendors()->exists()) {
                        $vendor = $user->vendors()->first();
                        
                        // Update the product with correct vendor ULID
                        $product->update(['vendor_id' => $vendor->id]);
                        
                        $this->info("✅ Fixed product {$product->id} - vendor_id: {$vendor->id}");
                        $fixedCount++;
                    } else {
                        $this->error("❌ Could not find vendor for product {$product->id}");
                        $errorCount++;
                    }
                } else {
                    // Verify the vendor_id is valid
                    $vendor = Vendor::find($product->vendor_id);
                    if (!$vendor) {
                        $this->warn("Product {$product->id} has invalid vendor_id: {$product->vendor_id}");
                        $errorCount++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("❌ Error fixing product {$product->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }
        
        $this->info("✅ Fix completed!");
        $this->info("📊 Results:");
        $this->info("   - Products fixed: {$fixedCount}");
        $this->info("   - Errors: {$errorCount}");
        
        return $errorCount === 0 ? 0 : 1;
    }
}
