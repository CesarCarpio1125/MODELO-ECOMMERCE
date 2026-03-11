<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupTempImages extends Command
{
    protected $signature = 'storage:cleanup-temp';
    protected $description = 'Clean up temporary images and check storage status';

    public function handle()
    {
        $this->info('Cleaning up temporary images...');
        
        $disk = Storage::disk('public');
        $tempPath = 'vendors/temp';
        
        if ($disk->exists($tempPath)) {
            $files = $disk->allFiles($tempPath);
            $deletedCount = 0;
            
            foreach ($files as $file) {
                if ($disk->delete($file)) {
                    $deletedCount++;
                    $this->line("Deleted: {$file}");
                }
            }
            
            $this->info("Deleted {$deletedCount} temporary files.");
        } else {
            $this->info('No temp directory found.');
        }
        
        // Show storage status
        $this->info("\nStorage Status:");
        $this->info('Public disk: ' . ($disk->exists('') ? 'OK' : 'ERROR'));
        
        // Check vendors directory
        if ($disk->exists('vendors')) {
            $this->info('Vendors directory: OK');
            $vendorDirs = $disk->directories('vendors');
            $this->info('Vendor directories: ' . count($vendorDirs));
        } else {
            $this->warn('Vendors directory: NOT FOUND');
        }
        
        return Command::SUCCESS;
    }
}
