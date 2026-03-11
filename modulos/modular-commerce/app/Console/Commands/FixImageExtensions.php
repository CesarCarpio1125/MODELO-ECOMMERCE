<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FixImageExtensions extends Command
{
    protected $signature = 'vendor:fix-image-extensions';
    protected $description = 'Fix vendor store images that have incorrect extensions in the database';

    public function handle()
    {
        $this->info('Starting image extension fix...');
        
        $vendors = \App\Modules\Vendor\Vendor::whereNotNull('store_image')->get();
        $fixed = 0;
        $errors = 0;

        foreach ($vendors as $vendor) {
            $dbPath = $vendor->store_image; // e.g., "vendors/01kjb39jwyhw4a8spe90nfv4m7/store_image.jpeg"
            
            if (!$dbPath) {
                continue;
            }

            // Extract vendor ID and old extension from DB path
            $parts = explode('/', $dbPath);
            $vendorId = $parts[1] ?? null;
            $filename = $parts[2] ?? '';
            $oldExtension = pathinfo($filename, PATHINFO_EXTENSION);

            if (!$vendorId) {
                $this->error("Invalid path for vendor {$vendor->id}: {$dbPath}");
                $errors++;
                continue;
            }

            // Get the actual file from storage
            $fullPath = storage_path('app/public/' . $dbPath);
            
            if (!File::exists($fullPath)) {
                $this->error("File not found for vendor {$vendor->id}: {$fullPath}");
                $errors++;
                continue;
            }

            // Get the real MIME type and extension from the file content
            $realMimeType = File::mimeType($fullPath);
            $actualExtension = null;

            // Map MIME types to extensions
            switch ($realMimeType) {
                case 'image/jpeg':
                    $actualExtension = 'jpg'; // Use jpg consistently
                    break;
                case 'image/png':
                    $actualExtension = 'png';
                    break;
                case 'image/gif':
                    $actualExtension = 'gif';
                    break;
                case 'image/webp':
                    $actualExtension = 'webp';
                    break;
                default:
                    $this->warn("Unknown MIME type {$realMimeType} for vendor {$vendor->id}");
                    $actualExtension = $oldExtension;
            }

            if ($actualExtension !== $oldExtension) {
                $this->info("Vendor {$vendor->id}: Changing extension from .{$oldExtension} to .{$actualExtension}");
                
                // Get the directory
                $directory = dirname($dbPath);
                
                // Build new path
                $newFilename = 'store_image.' . $actualExtension;
                $newPath = $directory . '/' . $newFilename;
                $newFullPath = storage_path('app/public/' . $newPath);

                // Rename the file
                if (File::move($fullPath, $newFullPath)) {
                    // Update database
                    $vendor->update(['store_image' => $newPath]);
                    $fixed++;
                    $this->info("  ✓ Fixed: {$dbPath} -> {$newPath}");
                } else {
                    $this->error("  ✗ Failed to move file for vendor {$vendor->id}");
                    $errors++;
                }
            } else {
                $this->line("Vendor {$vendor->id}: Extension already correct ({$oldExtension})");
            }
        }

        $this->info("Done! Fixed: {$fixed}, Errors: {$errors}");
        
        return 0;
    }
}

