<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Vendor\Vendor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DiagnoseVendorImages extends Command
{
    protected $signature = 'vendor:diagnose-images {--email= : Filter by user email}';
    protected $description = 'Diagnose vendor image issues';

    public function handle()
    {
        $query = Vendor::whereNotNull('store_image');
        
        if ($this->option('email')) {
            $query->whereHas('user', function ($q) {
                $q->where('email', $this->option('email'));
            });
        }

        $vendors = $query->with('user')->get();

        $this->info("Found {$vendors->count()} vendors with images\n");

        foreach ($vendors as $vendor) {
            $this->line("=== Vendor: {$vendor->store_name} (ID: {$vendor->id}) ===");
            $this->line("  User: {$vendor->user->email}");
            $this->line("  DB path: {$vendor->store_image}");
            
            // Check if file exists
            $fullPath = storage_path('app/public/' . $vendor->store_image);
            $exists = File::exists($fullPath);
            $this->line("  File exists: " . ($exists ? 'YES' : 'NO'));
            
            if ($exists) {
                $mimeType = File::mimeType($fullPath);
                $size = filesize($fullPath);
                $this->line("  MIME type: {$mimeType}");
                $this->line("  File size: {$size} bytes");
                
                // Get the URL that would be generated
                $url = \App\Helpers\ImageHelper::getImageUrl($vendor->store_image);
                $this->line("  Generated URL: {$url}");
            }
            
            // Show what the accessor returns
            $this->line("  store_image_url: " . ($vendor->store_image_url ?? 'NULL'));
            
            $this->line("");
        }

        return 0;
    }
}

