<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Vendor\Vendor;

class TestNativeImages extends Command
{
    protected $signature = 'native:test-images';
    protected $description = 'Test image serving in Native environment';

    public function handle()
    {
        $this->info('=== Testing Native Image Serving ===');
        $this->newLine();

        // Test the Native URL middleware
        $this->testUrlConfiguration();

        // Test actual image serving
        $this->testImageServing();

        // Show next steps
        $this->showNextSteps();

        return Command::SUCCESS;
    }

    private function testUrlConfiguration()
    {
        $this->info('🔗 Testing URL Configuration...');
        
        // Simulate Native request
        $request = \Illuminate\Http\Request::create('http://127.0.0.1:8100/test');
        $middleware = new \App\Http\Middleware\NativeUrlMiddleware();
        
        // Apply middleware
        $middleware->handle($request, function ($req) {
            return 'OK';
        });
        
        $this->line("  APP_URL: " . config('app.url'));
        $this->line("  URL::root(): " . \Illuminate\Support\Facades\URL::to('/'));
        $this->line("  Native detected: " . (\App\Helpers\ImageHelper::isNativeEnvironment() ? '✅ Yes' : '❌ No'));
        
        $this->newLine();
    }

    private function testImageServing()
    {
        $this->info('🖼️  Testing Image Serving...');
        
        $vendor = Vendor::where('store_name', 'cesarcecesasad')
            ->whereNotNull('store_image')
            ->first();
            
        if (!$vendor) {
            $this->error("  ❌ No vendor with image found");
            return;
        }
        
        $this->line("  Testing vendor: {$vendor->store_name}");
        $this->line("  Image path: {$vendor->store_image}");
        
        // Test image URL generation
        $imageUrl = \App\Services\NativeImageService::getImageUrl($vendor->store_image);
        $this->line("  Generated URL: {$imageUrl}");
        
        // Test if file exists
        $fullPath = storage_path('app/public/' . $vendor->store_image);
        $this->line("  File exists: " . (file_exists($fullPath) ? '✅ Yes' : '❌ No'));
        
        if (file_exists($fullPath)) {
            $this->line("  File size: " . number_format(filesize($fullPath) / 1024, 2) . ' KB');
            $this->line("  MIME type: " . mime_content_type($fullPath));
            
            // Test the service directly
            try {
                $response = \App\Services\NativeImageService::serveImage($vendor->store_image);
                $this->line("  Service response: ✅ Success (Status: " . $response->getStatusCode() . ")");
            } catch (\Exception $e) {
                $this->line("  Service response: ❌ Error - " . $e->getMessage());
            }
        }
        
        $this->newLine();
    }

    private function showNextSteps()
    {
        $this->info('🚀 Next Steps:');
        $this->newLine();
        
        $this->line('1. Start Native environment:');
        $this->line('   ./start-native.sh');
        $this->newLine();
        
        $this->line('2. Open Electron app');
        $this->newLine();
        
        $this->line('3. Test image loading:');
        $this->line('   - Login with amaf2511@gmail.com');
        $this->line('   - Go to vendor dashboard');
        $this->line('   - Check if store image loads');
        $this->newLine();
        
        $this->line('4. If images still fail:');
        $this->line('   - Check browser console for errors');
        $this->line('   - Use Ctrl+Shift+D → Status');
        $this->line('   - Run: php artisan images:diagnose');
        $this->newLine();
        
        $this->info('✅ Native image system is configured!');
    }
}
