<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Modules\Vendor\Vendor;

class FixVendorImages extends Command
{
    protected $signature = 'vendor:fix-images {--auto-fix : Automatically fix missing images} {--use-default : Use default image instead of creating empty directories}';
    protected $description = 'Fix vendors with missing image files';

    public function handle()
    {
        $this->info('🔍 Scanning for vendors with missing images...');
        
        $vendors = Vendor::whereNotNull('store_image')->get();
        $missingImages = [];
        $fixedImages = [];
        
        foreach ($vendors as $vendor) {
            if (!$vendor->store_image) {
                continue;
            }
            
            $fullPath = storage_path('app/public/' . $vendor->store_image);
            
            if (!file_exists($fullPath)) {
                $missingImages[] = [
                    'id' => $vendor->id,
                    'name' => $vendor->store_name,
                    'image_path' => $vendor->store_image,
                    'full_path' => $fullPath,
                ];
                
                if ($this->option('auto-fix')) {
                    $this->fixVendorImage($vendor, $fixedImages);
                }
            }
        }
        
        $this->info("\n📊 Results:");
        $this->info("Total vendors: " . $vendors->count());
        $this->info("Missing images: " . count($missingImages));
        $this->info("Fixed images: " . count($fixedImages));
        
        if (!empty($missingImages)) {
            $this->table(
                ['ID', 'Store Name', 'Image Path', 'Status'],
                array_map(function($item) use ($fixedImages) {
                    $fixed = collect($fixedImages)->firstWhere('id', $item['id']);
                    return [
                        $item['id'],
                        $item['name'],
                        $item['image_path'],
                        $fixed ? '✅ Fixed' : '❌ Missing'
                    ];
                }, $missingImages)
            );
        }
        
        if (empty($missingImages)) {
            $this->info("✅ All vendor images are present!");
        } elseif (!$this->option('auto-fix')) {
            $this->warn("\n💡 Run with --auto-fix to automatically fix missing images");
        }
        
        return 0;
    }
    
    private function fixVendorImage($vendor, &$fixedImages)
    {
        try {
            // Create directory
            $directory = dirname(storage_path('app/public/' . $vendor->store_image));
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Check if there's a recent uploaded image in temp directory first
            $tempDir = storage_path('app/public/vendors/temp');
            $recentImages = [];
            
            if (is_dir($tempDir)) {
                $files = scandir($tempDir);
                foreach ($files as $file) {
                    if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $filePath = $tempDir . '/' . $file;
                        if (filemtime($filePath) > (time() - 300)) { // Last 5 minutes
                            $recentImages[] = $filePath;
                        }
                    }
                }
            }
            
            // If there's a recent uploaded image, use it
            if (!empty($recentImages)) {
                $latestImage = end($recentImages);
                copy($latestImage, storage_path('app/public/' . $vendor->store_image));
                
                $fixedImages[] = [
                    'id' => $vendor->id,
                    'name' => $vendor->store_name,
                    'path' => $vendor->store_image,
                    'source' => 'uploaded_image',
                ];
                
                $this->line("✅ Fixed: {$vendor->store_name} (using uploaded image)");
                return;
            }
            
            // Only use default image if explicitly requested
            if ($this->option('use-default')) {
                $defaultImage = storage_path('app/public/vendors/01kjb39jwyhw4a8spe90nfv4m7/store_image.jpg');
                if (file_exists($defaultImage)) {
                    copy($defaultImage, storage_path('app/public/' . $vendor->store_image));
                    
                    $fixedImages[] = [
                        'id' => $vendor->id,
                        'name' => $vendor->store_name,
                        'path' => $vendor->store_image,
                        'source' => 'default_image',
                    ];
                    
                    $this->line("⚠️  Fixed: {$vendor->store_name} (using default image - PLEASE UPLOAD REAL IMAGE)");
                }
            } else {
                // Just create the directory structure for manual upload
                $fixedImages[] = [
                    'id' => $vendor->id,
                    'name' => $vendor->store_name,
                    'path' => $vendor->store_image,
                    'source' => 'directory_created',
                ];
                
                $this->line("📁 Created directory for: {$vendor->store_name} (PLEASE UPLOAD IMAGE MANUALLY)");
            }
        } catch (\Exception $e) {
            $this->error("❌ Failed to fix {$vendor->store_name}: " . $e->getMessage());
        }
    }
}
