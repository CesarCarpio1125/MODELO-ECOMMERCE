<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Vendor\Vendor;

class MonitorVendorCreation extends Command
{
    protected $signature = 'vendors:monitor-creation {--test}';
    protected $description = 'Monitor vendor creation process in Native vs Web';

    public function handle()
    {
        $this->info('=== Monitoring Vendor Creation Process ===');
        $this->newLine();

        if ($this->option('test')) {
            $this->testCreationProcess();
        } else {
            $this->analyzeCurrentVendors();
            $this->showCreationDifferences();
        }

        return Command::SUCCESS;
    }

    private function analyzeCurrentVendors()
    {
        $this->info('📊 Current Vendor Analysis:');
        
        $vendors = Vendor::with('user')->orderBy('created_at', 'desc')->get();
        
        foreach ($vendors as $vendor) {
            $this->line("\n--- Vendor: {$vendor->store_name} ---");
            $this->line("ID: {$vendor->id}");
            $this->line("User: {$vendor->user->email}");
            $this->line("Created: {$vendor->created_at}");
            $this->line("Image: " . ($vendor->store_image ?? 'NULL'));
            
            if ($vendor->store_image) {
                $this->checkImageIntegrity($vendor->store_image);
            }
            
            // Analyze creation pattern
            $this->analyzeCreationPattern($vendor);
        }
    }

    private function checkImageIntegrity(string $imagePath)
    {
        $fullPath = storage_path('app/public/' . $imagePath);
        
        $this->line("Image Analysis:");
        $this->line("  Expected: {$imagePath}");
        $this->line("  Full path: {$fullPath}");
        $this->line("  Exists: " . (file_exists($fullPath) ? '✅ Yes' : '❌ No'));
        
        if (file_exists($fullPath)) {
            $this->line("  Size: " . number_format(filesize($fullPath) / 1024, 2) . ' KB');
            $this->line("  MIME: " . mime_content_type($fullPath));
            
            // Check if it's a valid image
            $imageInfo = @getimagesize($fullPath);
            $this->line("  Valid: " . ($imageInfo !== false ? '✅ Yes' : '❌ No'));
            
            if ($imageInfo) {
                $this->line("  Dimensions: {$imageInfo[0]}x{$imageInfo[1]}");
            }
        }
    }

    private function analyzeCreationPattern(Vendor $vendor)
    {
        $this->line("Creation Pattern:");
        
        // Check if ID follows expected pattern
        $idPattern = '/^[a-f0-9]{26}$/';
        $validId = preg_match($idPattern, $vendor->id);
        $this->line("  ID Pattern: " . ($validId ? '✅ Valid ULID' : '❌ Invalid'));
        
        // Check if image path follows expected pattern
        if ($vendor->store_image) {
            $expectedPattern = "vendors/{$vendor->id}/store_image.";
            $correctPath = str_starts_with($vendor->store_image, $expectedPattern);
            $this->line("  Path Pattern: " . ($correctPath ? '✅ Correct' : '❌ Incorrect'));
            
            if (!$correctPath) {
                $this->line("  Expected: {$expectedPattern}[ext]");
                $this->line("  Actual: {$vendor->store_image}");
            }
        }
        
        // Check creation time
        $now = now();
        $diff = $now->diffInHours($vendor->created_at);
        $this->line("  Age: {$diff} hours ago");
    }

    private function showCreationDifferences()
    {
        $this->newLine();
        $this->info('🔍 Native vs Web Creation Differences:');
        $this->newLine();
        
        $this->line('WEB CREATION (Working):');
        $this->line('1. User uploads image via web form');
        $this->line('2. Laravel receives UploadedFile object');
        $this->line('3. Image stored in vendors/temp/');
        $this->line('4. Vendor created with ID');
        $this->line('5. Image moved to vendors/{id}/store_image.jpg');
        $this->line('6. Database updated with correct path');
        $this->newLine();
        
        $this->line('NATIVE CREATION (Problematic):');
        $this->line('1. User uploads image via Electron');
        $this->line('2. NativePHP may handle file differently');
        $this->line('3. Image may not be stored correctly');
        $this->line('4. Vendor created but image path broken');
        $this->line('5. Frontend shows incorrect URL');
        $this->line('6. 403/404 errors when loading image');
        $this->newLine();
        
        $this->info('🚨 Possible Issues in Native:');
        $this->line('• File upload handling in Electron');
        $this->line('• Temporary directory permissions');
        $this->line('• Storage disk configuration');
        $this->line('• Image move operation failure');
        $this->line('• Database transaction rollback');
    }

    private function testCreationProcess()
    {
        $this->info('🧪 Testing Vendor Creation Process...');
        
        // Test storage directories
        $tempPath = storage_path('app/public/vendors/temp');
        $this->line("Temp directory: {$tempPath}");
        $this->line("  Exists: " . (is_dir($tempPath) ? '✅ Yes' : '❌ No'));
        $this->line("  Writable: " . (is_writable($tempPath) ? '✅ Yes' : '❌ No'));
        
        $vendorsPath = storage_path('app/public/vendors');
        $this->line("Vendors directory: {$vendorsPath}");
        $this->line("  Exists: " . (is_dir($vendorsPath) ? '✅ Yes' : '❌ No'));
        $this->line("  Writable: " . (is_writable($vendorsPath) ? '✅ Yes' : '❌ No'));
        
        // Test storage disk
        $this->newLine();
        $this->line('Storage Disk Test:');
        try {
            $testFile = 'test_' . uniqid() . '.txt';
            \Storage::disk('public')->put($testFile, 'test content');
            $exists = \Storage::disk('public')->exists($testFile);
            $this->line("  Write test: " . ($exists ? '✅ Success' : '❌ Failed'));
            
            if ($exists) {
                \Storage::disk('public')->delete($testFile);
                $this->line("  Delete test: ✅ Success");
            }
        } catch (\Exception $e) {
            $this->error("  Storage error: " . $e->getMessage());
        }
    }
}
