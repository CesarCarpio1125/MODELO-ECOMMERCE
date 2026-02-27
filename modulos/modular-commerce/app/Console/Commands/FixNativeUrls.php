<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class FixNativeUrls extends Command
{
    protected $signature = 'native:fix-urls';
    protected $description = 'Fix URLs for Native/Electron environment';

    public function handle()
    {
        $this->info('=== Fixing Native URLs ===');
        $this->newLine();

        // Force URL configuration for Native
        $this->forceNativeUrlConfiguration();

        // Test image URLs
        $this->testImageUrls();

        // Show current configuration
        $this->showCurrentConfiguration();

        return Command::SUCCESS;
    }

    private function forceNativeUrlConfiguration()
    {
        $this->info('🔧 Configuring URLs for Native...');
        
        // Force the URL configuration for Native
        $nativeUrl = 'http://127.0.0.1:8100';
        
        // Set URL configuration at runtime
        URL::forceRootUrl($nativeUrl);
        URL::forceScheme('http');
        
        // Update config
        config(['app.url' => $nativeUrl]);
        
        $this->line("  ✅ Forced URL to: {$nativeUrl}");
        $this->newLine();
    }

    private function testImageUrls()
    {
        $this->info('🖼️  Testing Image URLs...');
        
        $vendor = \App\Modules\Vendor\Vendor::where('store_name', 'cesarcecesasad')
            ->whereNotNull('store_image')
            ->first();
            
        if ($vendor) {
            $imageUrl = \App\Services\NativeImageService::getImageUrl($vendor->store_image);
            $this->line("  Vendor: {$vendor->store_name}");
            $this->line("  Image path: {$vendor->store_image}");
            $this->line("  Generated URL: {$imageUrl}");
            $this->line("  Full URL: " . url($imageUrl));
            
            // Test if the file actually exists
            $fullPath = storage_path('app/public/' . $vendor->store_image);
            $this->line("  File exists: " . (file_exists($fullPath) ? '✅ Yes' : '❌ No'));
            
            if (file_exists($fullPath)) {
                $this->line("  File size: " . number_format(filesize($fullPath) / 1024, 2) . ' KB');
            }
        } else {
            $this->line("  ❌ No vendor with image found for testing");
        }
        
        $this->newLine();
    }

    private function showCurrentConfiguration()
    {
        $this->info('⚙️  Current Configuration:');
        $this->newLine();
        
        $this->line('URL Configuration:');
        $this->line("  APP_URL: " . config('app.url'));
        $this->line("  URL::root(): " . URL::to('/'));
        $this->line("  asset(): " . asset('storage/test.jpg'));
        $this->line("  url(): " . url('/storage/test.jpg'));
        
        $this->newLine();
        $this->line('Environment:');
        $this->line("  Environment: " . app()->environment());
        $this->line("  Native Running: " . (\App\Helpers\ImageHelper::isNativeEnvironment() ? 'Yes' : 'No'));
        
        $this->newLine();
        $this->info('🚀 To apply these fixes permanently:');
        $this->line('1. Add to your .env file:');
        $this->line('   APP_URL=http://127.0.0.1:8100');
        $this->line('2. Or run this command before starting Native');
        $this->line('3. Restart the Native application');
    }
}
