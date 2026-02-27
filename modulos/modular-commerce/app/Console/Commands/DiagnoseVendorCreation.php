<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Modules\Vendor\Vendor;

class DiagnoseVendorCreation extends Command
{
    protected $signature = 'vendor:diagnose-creation {vendor_id?}';
    protected $description = 'Diagnose vendor creation issues';

    public function handle()
    {
        $vendorId = $this->argument('vendor_id');
        
        if ($vendorId) {
            $this->diagnoseSpecificVendor($vendorId);
        } else {
            $this->diagnoseRecentVendors();
        }
        
        return 0;
    }
    
    private function diagnoseSpecificVendor($vendorId)
    {
        $this->info("=== Diagnosing Vendor: {$vendorId} ===");
        
        $vendor = Vendor::find($vendorId);
        if (!$vendor) {
            $this->error("Vendor not found");
            return;
        }
        
        $this->line("Vendor ID: {$vendor->id}");
        $this->line("Store Name: {$vendor->store_name}");
        $this->line("Store Image: {$vendor->store_image}");
        $this->line("Created At: {$vendor->created_at}");
        
        if ($vendor->store_image) {
            $this->line("\n--- Image Analysis ---");
            $this->line("DB Path: {$vendor->store_image}");
            
            $fullPath = storage_path('app/public/' . $vendor->store_image);
            $this->line("Full Path: {$fullPath}");
            $this->line("File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO'));
            
            if (file_exists($fullPath)) {
                $this->line("File Size: " . filesize($fullPath) . " bytes");
                $this->line("File Type: " . mime_content_type($fullPath));
            }
            
            $storageExists = Storage::disk('public')->exists($vendor->store_image);
            $this->line("Storage Exists: " . ($storageExists ? 'YES' : 'NO'));
            
            // Test URL
            $url = url('/storage/' . $vendor->store_image);
            $this->line("Public URL: {$url}");
            
            // Test HTTP response
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $headers = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $this->line("HTTP Response: {$httpCode}");
        }
    }
    
    private function diagnoseRecentVendors()
    {
        $this->info("=== Recent Vendors (Last 24 Hours) ===");
        
        $recentVendors = Vendor::where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->get();
            
        if ($recentVendors->isEmpty()) {
            $this->info("No vendors created in the last 24 hours");
            return;
        }
        
        foreach ($recentVendors as $vendor) {
            $this->line("\n--- Vendor: {$vendor->store_name} ---");
            $this->line("ID: {$vendor->id}");
            $this->line("Image: {$vendor->store_image}");
            $this->line("Created: {$vendor->created_at}");
            
            $hasFile = $vendor->store_image && Storage::disk('public')->exists($vendor->store_image);
            $this->line("File Exists: " . ($hasFile ? 'YES' : 'NO'));
            
            if (!$hasFile && $vendor->store_image) {
                $this->error("❌ MISSING FILE - Vendor exists but image file is missing");
            }
        }
    }
}
