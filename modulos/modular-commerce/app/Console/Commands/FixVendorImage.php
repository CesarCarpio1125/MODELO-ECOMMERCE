<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Vendor\Vendor;
use App\Modules\Vendor\Services\VendorService;

class FixVendorImage extends Command
{
    protected $signature = 'vendor:fix-image {--vendor=} {--image=}';
    protected $description = 'Fix vendor image issues';

    public function handle()
    {
        $this->info('=== Fixing Vendor Images ===');
        $this->newLine();

        // Show current status
        $this->showVendorImageStatus();

        // Fix specific vendor if requested
        if ($this->option('vendor')) {
            $this->fixSpecificVendor();
        }

        // Show where images are handled
        $this->showImageHandlingFlow();

        return Command::SUCCESS;
    }

    private function showVendorImageStatus()
    {
        $this->info('📊 Current Vendor Image Status:');
        
        $vendors = Vendor::with('user')->get();
        
        foreach ($vendors as $vendor) {
            $hasImage = $vendor->store_image ? '✅ Yes' : '❌ No';
            $imagePath = $vendor->store_image ?? 'NULL';
            
            $this->line("  Vendor: {$vendor->store_name}");
            $this->line("    User: {$vendor->user->email}");
            $this->line("    Image: {$hasImage}");
            $this->line("    Path: {$imagePath}");
            
            if ($vendor->store_image) {
                $fullPath = storage_path('app/public/' . $vendor->store_image);
                $exists = file_exists($fullPath) ? '✅' : '❌';
                $this->line("    File exists: {$exists}");
            }
            
            $this->newLine();
        }
    }

    private function fixSpecificVendor()
    {
        $vendorId = $this->option('vendor');
        $imagePath = $this->option('image');
        
        if (!$vendorId) {
            $this->error('Vendor ID is required');
            return;
        }
        
        $vendor = Vendor::find($vendorId);
        
        if (!$vendor) {
            $this->error("Vendor with ID {$vendorId} not found");
            return;
        }
        
        $this->info("🔧 Fixing vendor: {$vendor->store_name}");
        
        if ($imagePath) {
            // Set specific image path
            if (file_exists(storage_path('app/public/' . $imagePath))) {
                $vendor->store_image = $imagePath;
                $vendor->save();
                $this->info("  ✅ Image set to: {$imagePath}");
            } else {
                $this->error("  ❌ Image file not found: {$imagePath}");
            }
        } else {
            // Clear image
            $vendor->store_image = null;
            $vendor->save();
            $this->info("  ✅ Image cleared");
        }
    }

    private function showImageHandlingFlow()
    {
        $this->info('🔄 Image Handling Flow:');
        $this->newLine();
        
        $this->line('1. FRONTEND (VendorEditForm.vue):');
        $this->line('   - User selects image via <input type="file">');
        $this->line('   - handleImageUpload() stores file in form.store_image');
        $this->line('   - Form submits to vendor.update route');
        $this->newLine();
        
        $this->line('2. BACKEND (VendorController@update):');
        $this->line('   - UpdateVendorRequest validates image (nullable, image, max:2048)');
        $this->line('   - Calls VendorService->updateVendor()');
        $this->newLine();
        
        $this->line('3. SERVICE (VendorService@updateVendor):');
        $this->line('   - Checks if store_image is UploadedFile');
        $this->line('   - Deletes old image if exists');
        $this->line('   - Calls storeVendorImage() to save new image');
        $this->line('   - Calls moveVendorImage() to move from temp to permanent');
        $this->newLine();
        
        $this->line('4. STORAGE PATHS:');
        $this->line('   - Temp: storage/app/public/vendors/temp/');
        $this->line('   - Final: storage/app/public/vendors/{vendor_id}/store_image.{ext}');
        $this->newLine();
        
        $this->line('5. SERVING (NativeImageService):');
        $this->line('   - Route: /storage/{path}');
        $this->line('   - Serves from: storage/app/public/{path}');
        $this->newLine();
        
        $this->info('🚀 To add image to vendor manually:');
        $this->line('1. Place image in: storage/app/public/vendors/{vendor_id}/');
        $this->line('2. Name it: store_image.jpg (or .png, .webp)');
        $this->line('3. Update database: UPDATE vendors SET store_image = "vendors/{vendor_id}/store_image.jpg" WHERE id = "{vendor_id}";');
        $this->line('4. Or use: php artisan vendor:fix-image --vendor={vendor_id} --image="vendors/{vendor_id}/store_image.jpg"');
    }
}
