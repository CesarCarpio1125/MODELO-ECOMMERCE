<?php

namespace App\Console\Commands;

use App\Modules\Vendor\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateVendorImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendors:migrate-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate vendor images from temp location to permanent vendor folder';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting vendor images migration...');

        $vendors = Vendor::whereNotNull('store_image')->get();
        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($vendors as $vendor) {
            $currentPath = $vendor->store_image;

            // Check if image is in temp folder
            if (str_contains($currentPath, 'vendors/temp/')) {
                $this->info("Processing vendor {$vendor->id}: {$currentPath}");

                // Try to find the actual file
                $filename = basename($currentPath);
                
                // Check if file exists in temp
                if (Storage::disk('public')->exists($currentPath)) {
                    // Move to permanent location
                    $newPath = 'vendors/'.$vendor->id.'/store_image.'.pathinfo($filename, PATHINFO_EXTENSION);
                    
                    try {
                        Storage::disk('public')->move($currentPath, $newPath);
                        $vendor->update(['store_image' => $newPath]);
                        $this->info("  Migrated to: {$newPath}");
                        $migrated++;
                    } catch (\Exception $e) {
                        $this->error("  Error: {$e->getMessage()}");
                        $errors++;
                    }
                } else {
                    // Try to find the file by searching in temp directory
                    $found = false;
                    $tempFiles = Storage::disk('public')->files('vendors/temp');
                    
                    foreach ($tempFiles as $tempFile) {
                        $tempFilename = basename($tempFile);
                        // Check if filename starts with the same prefix
                        if (str_starts_with($tempFilename, substr($filename, 0, 4))) {
                            $newPath = 'vendors/'.$vendor->id.'/store_image.'.pathinfo($tempFilename, PATHINFO_EXTENSION);
                            
                            try {
                                Storage::disk('public')->move($tempFile, $newPath);
                                $vendor->update(['store_image' => $newPath]);
                                $this->info("  Found and migrated: {$tempFilename} -> {$newPath}");
                                $migrated++;
                                $found = true;
                                break;
                            } catch (\Exception $e) {
                                $this->error("  Error: {$e->getMessage()}");
                                $errors++;
                            }
                        }
                    }
                    
                    if (!$found) {
                        $this->warn("  File not found in temp: {$filename}");
                        $skipped++;
                    }
                }
            } else {
                // Already in permanent location
                $this->info("Vendor {$vendor->id}: Already in permanent location");
                $skipped++;
            }
        }

        $this->info("Migration complete: {$migrated} migrated, {$skipped} skipped, {$errors} errors");

        return Command::SUCCESS;
    }
}

