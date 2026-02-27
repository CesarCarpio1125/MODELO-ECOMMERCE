<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Vendor\Vendor;

class Diagnose403Error extends Command
{
    protected $signature = 'images:diagnose-403 {--vendor=}';
    protected $description = 'Diagnose 403 Forbidden errors in image serving';

    public function handle()
    {
        $this->info('=== Diagnosing 403 Forbidden Errors ===');
        $this->newLine();

        // Check specific vendor if provided
        if ($this->option('vendor')) {
            $this->diagnoseVendor($this->option('vendor'));
        } else {
            $this->checkAllVendors();
            $this->show403Explanation();
        }

        return Command::SUCCESS;
    }

    private function diagnoseVendor(string $vendorId)
    {
        $this->info("🔍 Diagnosing Vendor ID: {$vendorId}");
        
        // Check if vendor exists
        $vendor = Vendor::find($vendorId);
        
        if (!$vendor) {
            $this->error("❌ Vendor {$vendorId} does not exist in database");
            $this->showPossibleSolutions($vendorId);
            return;
        }
        
        $this->info("✅ Vendor found: {$vendor->store_name}");
        $this->line("User: {$vendor->user->email}");
        $this->line("Image: " . ($vendor->store_image ?? 'NULL'));
        
        if ($vendor->store_image) {
            $this->checkImageFile($vendor->store_image);
        }
    }

    private function checkAllVendors()
    {
        $this->info('📊 Checking all vendors...');
        
        $vendors = Vendor::with('user')->get();
        
        foreach ($vendors as $vendor) {
            $this->line("\n--- Vendor: {$vendor->store_name} ---");
            $this->line("ID: {$vendor->id}");
            $this->line("User: {$vendor->user->email}");
            
            if ($vendor->store_image) {
                $this->checkImageFile($vendor->store_image);
            } else {
                $this->line("Image: ❌ No image");
            }
        }
    }

    private function checkImageFile(string $imagePath)
    {
        $fullPath = storage_path('app/public/' . $imagePath);
        
        $this->line("Expected path: {$imagePath}");
        $this->line("Full path: {$fullPath}");
        $this->line("File exists: " . (file_exists($fullPath) ? '✅ Yes' : '❌ No'));
        
        if (file_exists($fullPath)) {
            $this->line("File size: " . number_format(filesize($fullPath) / 1024, 2) . ' KB');
            $this->line("MIME type: " . mime_content_type($fullPath));
            
            // Check security
            $realBasePath = realpath(storage_path('app/public'));
            $realFullPath = realpath($fullPath);
            
            if ($realFullPath) {
                $isAllowed = str_starts_with($realFullPath, $realBasePath);
                $this->line("Security check: " . ($isAllowed ? '✅ Pass' : '❌ Fail'));
            } else {
                $this->line("Security check: ❌ Path resolution failed");
            }
        }
    }

    private function showPossibleSolutions(string $vendorId)
    {
        $this->newLine();
        $this->info('🔧 Possible Solutions:');
        
        $this->line('1. Clear frontend cache:');
        $this->line('   - Ctrl+Shift+D → Clear Cache');
        $this->line('   - Ctrl+Shift+R to refresh');
        
        $this->line('2. Check if this is a stale vendor ID:');
        $this->line('   - Old vendor that was deleted');
        $this->line('   - Temporary ID from failed creation');
        $this->line('   - Cache corruption');
        
        $this->line('3. Verify correct vendor IDs:');
        $vendors = Vendor::pluck('store_name', 'id');
        foreach ($vendors as $id => $name) {
            $this->line("   - {$id}: {$name}");
        }
        
        $this->line('4. Fix NativeImageService:');
        $this->line('   - Better error handling for missing files');
        $this->line('   - Return 404 instead of 403 for missing files');
    }

    private function show403Explanation()
    {
        $this->newLine();
        $this->info('🚨 Why 403 Forbidden Errors Occur:');
        $this->newLine();
        
        $this->line('1. **File Not Found**: NativeImageService tries to access non-existent file');
        $this->line('2. **Path Resolution**: realpath() returns FALSE for missing files');
        $this->line('3. **Security Check**: !realPath triggers 403 instead of 404');
        $this->line('4. **Frontend Cache**: Shows URLs with old/invalid vendor IDs');
        $this->newLine();
        
        $this->info('📋 Backend Agent - Service Layer Fix:');
        $this->line('• Improve error handling in NativeImageService');
        $this->line('• Return 404 for missing files, not 403');
        $this->line('• Add better logging for debugging');
        $this->newLine();
        
        $this->info('🎨 Frontend Agent - Cache Management:');
        $this->line('• Clear stale vendor data from Vue cache');
        $this->line('• Refresh vendor list after database changes');
        $this->line('• Handle missing images gracefully');
        $this->newLine();
        
        $this->info('🚀 Immediate Fix:');
        $this->line('1. Clear all caches');
        $this->line('2. Restart Electron');
        $this->line('3. Verify correct vendor IDs');
        $this->line('4. Test image URLs');
    }
}
