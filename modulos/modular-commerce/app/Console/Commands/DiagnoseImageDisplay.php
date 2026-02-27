<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Vendor\Vendor;

class DiagnoseImageDisplay extends Command
{
    protected $signature = 'images:diagnose-display {--test-url=}';
    protected $description = 'Diagnose why some images don\'t display in Native/Electron';

    public function handle()
    {
        $this->info('=== Diagnosing Image Display Issues ===');
        $this->newLine();

        // Test both vendor images
        $this->testVendorImages();

        // Test specific URL if provided
        if ($this->option('test-url')) {
            $this->testSpecificUrl($this->option('test-url'));
        }

        // Show Native image serving process
        $this->showNativeImageProcess();

        return Command::SUCCESS;
    }

    private function testVendorImages()
    {
        $this->info('🖼️  Testing Vendor Images:');
        
        $vendors = Vendor::whereNotNull('store_image')->get();
        
        foreach ($vendors as $vendor) {
            $this->line("Vendor: {$vendor->store_name} (ID: {$vendor->id})");
            $this->line("User: {$vendor->user->email}");
            
            $imageUrl = 'http://127.0.0.1:8100/storage/' . $vendor->store_image;
            $this->line("Image URL: {$imageUrl}");
            
            // Test file existence
            $fullPath = storage_path('app/public/' . $vendor->store_image);
            $this->line("File path: {$fullPath}");
            $this->line("File exists: " . (file_exists($fullPath) ? '✅ Yes' : '❌ No'));
            
            if (file_exists($fullPath)) {
                // Test file properties
                $fileSize = filesize($fullPath);
                $mimeType = mime_content_type($fullPath);
                $this->line("File size: " . number_format($fileSize / 1024, 2) . ' KB');
                $this->line("MIME type: {$mimeType}");
                
                // Test if it's a valid image
                if ($this->isValidImage($fullPath)) {
                    $this->line("Image validity: ✅ Valid");
                } else {
                    $this->line("Image validity: ❌ Invalid or corrupted");
                }
                
                // Test HTTP response
                $this->testHttpResponse($imageUrl);
            }
            
            $this->newLine();
        }
    }

    private function isValidImage(string $path): bool
    {
        try {
            $imageInfo = @getimagesize($path);
            return $imageInfo !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function testHttpResponse(string $url)
    {
        $this->line("Testing HTTP response...");
        
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET',
                    'header' => "User-Agent: NativePHP\r\n"
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                $this->line("HTTP response: ✅ Success");
                
                // Check if we got image data
                if ($this->isValidImageData($response)) {
                    $this->line("Response type: ✅ Valid image data");
                } else {
                    $this->line("Response type: ❌ Not image data");
                    $this->line("Response preview: " . substr($response, 0, 200));
                }
            } else {
                $this->line("HTTP response: ❌ Failed");
                
                // Get more detailed error info
                $error = error_get_last();
                if ($error) {
                    $this->line("Error: " . $error['message']);
                }
            }
        } catch (\Exception $e) {
            $this->line("HTTP response: ❌ Exception - " . $e->getMessage());
        }
    }

    private function isValidImageData(string $data): bool
    {
        // Check if data starts with image signatures
        $signatures = [
            'jpeg' => "\xFF\xD8\xFF",
            'png' => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
            'gif' => "GIF87a",
            'webp' => "RIFF"
        ];
        
        foreach ($signatures as $type => $signature) {
            if (str_starts_with($data, $signature)) {
                return true;
            }
        }
        
        return false;
    }

    private function testSpecificUrl(string $url)
    {
        $this->info("🌐 Testing Specific URL: {$url}");
        
        // Parse URL to get path
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        
        // Remove /storage/ prefix to get file path
        $filePath = str_replace('/storage/', '', $path);
        $fullPath = storage_path('app/public/' . $filePath);
        
        $this->line("File path: {$fullPath}");
        $this->line("File exists: " . (file_exists($fullPath) ? '✅ Yes' : '❌ No'));
        
        if (file_exists($fullPath)) {
            $this->testHttpResponse($url);
        }
        
        $this->newLine();
    }

    private function showNativeImageProcess()
    {
        $this->info('🔄 Native Image Serving Process:');
        $this->newLine();
        
        $this->line('1. Electron requests: http://127.0.0.1:8100/storage/vendors/{id}/image.jpg');
        $this->line('2. Laravel route: /storage/{path} → NativeImageService::serveImage()');
        $this->line('3. Security check: Path validation and directory traversal prevention');
        $this->line('4. File check: Verify file exists in storage/app/public/');
        $this->line('5. MIME validation: Ensure file is actually an image');
        $this->line('6. Headers: Set proper Content-Type and CORS headers');
        $this->line('7. Response: Return file with proper headers');
        $this->newLine();
        
        $this->info('🔍 Common Issues:');
        $this->line('• File exists but is corrupted');
        $this->line('• Wrong MIME type in headers');
        $this->line('• CORS issues in Electron');
        $this->line('• Path traversal security blocking');
        $this->line('• Native URL middleware not working');
        $this->newLine();
        
        $this->info('🚀 To debug further:');
        $this->line('1. Check browser console for errors');
        $this->line('2. Check Laravel logs: tail -f storage/logs/laravel.log');
        $this->line('3. Test with curl: curl -v "http://127.0.0.1:8100/storage/path/to/image"');
        $this->line('4. Check if NativeUrlMiddleware is working');
    }
}
