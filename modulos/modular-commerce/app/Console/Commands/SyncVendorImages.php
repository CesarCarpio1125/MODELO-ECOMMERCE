<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Vendor\Vendor;

class SyncVendorImages extends Command
{
    protected $signature = 'vendors:sync-images {--fix-urls}';
    protected $description = 'Sync vendor image URLs and fix cache issues';

    public function handle()
    {
        $this->info('=== Syncing Vendor Image URLs ===');
        $this->newLine();

        // Show current status
        $this->showCurrentStatus();

        // Fix URLs if requested
        if ($this->option('fix-urls')) {
            $this->fixIncorrectUrls();
        }

        // Show how to clear frontend cache
        $this->showCacheClearingSteps();

        return Command::SUCCESS;
    }

    private function showCurrentStatus()
    {
        $this->info('📊 Current Vendor Status:');
        
        $vendors = Vendor::with('user')->get();
        
        foreach ($vendors as $vendor) {
            $this->line("Vendor: {$vendor->store_name}");
            $this->line("  ID: {$vendor->id}");
            $this->line("  User: {$vendor->user->email}");
            $this->line("  Image: " . ($vendor->store_image ? '✅ Yes' : '❌ No'));
            
            if ($vendor->store_image) {
                $expectedUrl = 'http://127.0.0.1:8100/storage/' . $vendor->store_image;
                $this->line("  Correct URL: {$expectedUrl}");
                
                // Check if file exists
                $fullPath = storage_path('app/public/' . $vendor->store_image);
                $fileExists = file_exists($fullPath) ? '✅' : '❌';
                $this->line("  File exists: {$fileExists}");
            }
            
            $this->newLine();
        }
    }

    private function fixIncorrectUrls()
    {
        $this->info('🔧 Checking for incorrect URLs...');
        
        // This would be used if there were vendors with incorrect image paths
        $vendors = Vendor::whereNotNull('store_image')->get();
        
        foreach ($vendors as $vendor) {
            // Verify the image path matches the vendor ID
            $expectedPath = "vendors/{$vendor->id}/store_image.jpg";
            $currentPath = $vendor->store_image;
            
            if ($currentPath !== $expectedPath) {
                $this->line("Vendor {$vendor->store_name} has incorrect path:");
                $this->line("  Current: {$currentPath}");
                $this->line("  Expected: {$expectedPath}");
                
                // Check if file exists at expected path
                $expectedFullPath = storage_path('app/public/' . $expectedPath);
                if (file_exists($expectedFullPath)) {
                    $this->line("  ✅ File exists at expected path, updating database...");
                    $vendor->store_image = $expectedPath;
                    $vendor->save();
                    $this->line("  ✅ Database updated");
                } else {
                    $this->line("  ❌ File does not exist at expected path");
                }
            }
        }
    }

    private function showCacheClearingSteps()
    {
        $this->info('🧹 How to Clear Frontend Cache:');
        $this->newLine();
        
        $this->line('1. In Electron app:');
        $this->line('   - Press Ctrl+Shift+D to open debug panel');
        $this->line('   - Click "Clear Cache" button');
        $this->line('   - Refresh the page');
        $this->newLine();
        
        $this->line('2. In browser:');
        $this->line('   - Press Ctrl+Shift+R to hard refresh');
        $this->line('   - Or Ctrl+F5 to clear cache and refresh');
        $this->newLine();
        
        $this->line('3. Restart Native/Electron:');
        $this->line('   - Close Electron app completely');
        $this->line('   - Run: ./start-native.sh');
        $this->line('   - Login again');
        $this->newLine();
        
        $this->line('4. Clear Laravel cache:');
        $this->line('   php artisan cache:clear');
        $this->line('   php artisan config:clear');
        $this->line('   php artisan view:clear');
        $this->line('   php artisan route:clear');
        $this->newLine();
        
        $this->info('🔍 Why this happens:');
        $this->line('• Vue.js caches component data');
        $this->line('• Inertia.js shares state across requests');
        $this->line('• Electron may cache HTTP responses');
        $this->line('• Vendor IDs might change during development');
        $this->newLine();
        
        $this->info('🚀 Recommended fix:');
        $this->line('1. Clear all caches');
        $this->line('2. Restart Electron');
        $this->line('3. Login fresh');
        $this->line('4. Check if URLs are correct');
    }
}
