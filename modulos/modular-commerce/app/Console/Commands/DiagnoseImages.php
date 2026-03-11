<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Modules\Vendor\Vendor;

class DiagnoseImages extends Command
{
    protected $signature = 'images:diagnose {--fix}';
    protected $description = 'Diagnose and fix image issues in Native environment';

    public function handle()
    {
        $this->info('=== Native Image Diagnosis ===');
        $this->newLine();

        // Check storage directory
        $this->checkStorageDirectory();

        // Check vendor images
        $this->checkVendorImages();

        // Test image serving
        $this->testImageServing();

        // Fix issues if requested
        if ($this->option('fix')) {
            $this->fixImageIssues();
        }

        return Command::SUCCESS;
    }

    private function checkStorageDirectory()
    {
        $this->info('📁 Checking Storage Directory:');
        
        $storagePath = storage_path('app/public');
        $this->line("  Path: {$storagePath}");
        $this->line("  Exists: " . (is_dir($storagePath) ? '✅ Yes' : '❌ No'));
        $this->line("  Writable: " . (is_writable($storagePath) ? '✅ Yes' : '❌ No'));
        
        if (is_dir($storagePath)) {
            $this->line("  Contents:");
            $this->listDirectoryContents($storagePath);
        }
        
        $this->newLine();
    }

    private function listDirectoryContents($path, $indent = 0)
    {
        $items = scandir($path);
        $prefix = str_repeat('  ', $indent);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $path . '/' . $item;
            $isDir = is_dir($fullPath);
            $icon = $isDir ? '📁' : '📄';
            $this->line("{$prefix}{$icon} {$item}");
            
            if ($isDir && $indent < 2) {
                $this->listDirectoryContents($fullPath, $indent + 1);
            }
        }
    }

    private function checkVendorImages()
    {
        $this->info('🏪 Checking Vendor Images:');
        
        $vendors = Vendor::with('user')->get();
        
        foreach ($vendors as $vendor) {
            $this->line("  Vendor: {$vendor->store_name} ({$vendor->user->email})");
            
            if ($vendor->store_image) {
                $imagePath = storage_path('app/public/' . $vendor->store_image);
                $this->line("    Image: {$vendor->store_image}");
                $this->line("    Path: {$imagePath}");
                $this->line("    Exists: " . (file_exists($imagePath) ? '✅ Yes' : '❌ No'));
                
                if (file_exists($imagePath)) {
                    $this->line("    Size: " . number_format(filesize($imagePath) / 1024, 2) . ' KB');
                    $this->line("    MIME: " . mime_content_type($imagePath));
                }
            } else {
                $this->line("    Image: ❌ No image set");
            }
            
            $this->newLine();
        }
    }

    private function testImageServing()
    {
        $this->info('🌐 Testing Image Serving:');
        
        $vendors = Vendor::whereNotNull('store_image')->get();
        
        foreach ($vendors as $vendor) {
            $url = url('/storage/' . $vendor->store_image);
            $this->line("  Testing: {$url}");
            
            // Test with curl or file_get_contents
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 5,
                        'method' => 'GET'
                    ]
                ]);
                
                $response = @file_get_contents($url, false, $context);
                
                if ($response !== false) {
                    $this->line("    ✅ Success");
                } else {
                    $this->line("    ❌ Failed");
                }
            } catch (\Exception $e) {
                $this->line("    ❌ Error: " . $e->getMessage());
            }
        }
        
        $this->newLine();
    }

    private function fixImageIssues()
    {
        $this->info('🔧 Fixing Image Issues:');
        
        // Create storage directories if they don't exist
        $directories = [
            storage_path('app/public/vendors'),
            storage_path('app/public/products'),
            storage_path('app/public/categories'),
        ];
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
                $this->line("  ✅ Created directory: {$directory}");
            }
        }
        
        // Fix vendor image paths
        $vendors = Vendor::whereNotNull('store_image')->get();
        
        foreach ($vendors as $vendor) {
            $imagePath = storage_path('app/public/' . $vendor->store_image);
            
            if (!file_exists($imagePath)) {
                $this->line("  ⚠️  Missing image for vendor: {$vendor->store_name}");
                
                // Try to find the image in different locations
                $possiblePaths = [
                    'vendors/' . basename($vendor->store_image),
                    'images/vendors/' . basename($vendor->store_image),
                    'vendor-images/' . basename($vendor->store_image),
                ];
                
                foreach ($possiblePaths as $possiblePath) {
                    $testPath = storage_path('app/public/' . $possiblePath);
                    if (file_exists($testPath)) {
                        $this->line("    ✅ Found at: {$possiblePath}");
                        $vendor->store_image = $possiblePath;
                        $vendor->save();
                        break;
                    }
                }
            }
        }
        
        $this->newLine();
        $this->info('✅ Image fixes completed');
    }
}
