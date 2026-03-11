<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DiagnoseNativeImageUpload extends Command
{
    protected $signature = 'diagnose:native-image-upload';
    protected $description = 'Diagnose NativePHP image upload issues';

    public function handle()
    {
        $this->info('🔍 Diagnosing NativePHP Image Upload Issues...');
        $this->newLine();

        // Test 1: Check storage configuration
        $this->testStorageConfiguration();

        // Test 2: Test file upload simulation
        $this->testFileUploadSimulation();

        // Test 3: Check permissions
        $this->testPermissions();

        // Test 4: Check NativePHP specific issues
        $this->testNativePHPSpecifics();

        $this->newLine();
        $this->info('📋 Diagnosis complete. See results above.');

        return 0;
    }

    private function testStorageConfiguration()
    {
        $this->section('Storage Configuration');
        
        $this->line("✅ Public disk driver: " . config('filesystems.disks.public.driver'));
        $this->line("✅ Public disk root: " . config('filesystems.disks.public.root'));
        $this->line("✅ Public URL: " . config('filesystems.disks.public.url'));
        
        // Test write permissions
        $testFile = 'vendors/temp/test_write_permissions.txt';
        try {
            Storage::disk('public')->put($testFile, 'test');
            $this->line("✅ Can write to storage: YES");
            Storage::disk('public')->delete($testFile);
        } catch (\Exception $e) {
            $this->error("❌ Cannot write to storage: " . $e->getMessage());
        }
    }

    private function testFileUploadSimulation()
    {
        $this->section('File Upload Simulation');
        
        // Create a test image
        $testImageData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A');
        
        $tempPath = sys_get_temp_dir() . '/test_image.jpg';
        file_put_contents($tempPath, $testImageData);
        
        try {
            // Simulate UploadedFile
            $file = new \Illuminate\Http\UploadedFile(
                $tempPath,
                'test_image.jpg',
                'image/jpeg',
                null,
                true
            );
            
            $this->line("✅ Test file created: " . $file->getPathname());
            $this->line("✅ Test file size: " . $file->getSize() . " bytes");
            $this->line("✅ Test file MIME: " . $file->getMimeType());
            $this->line("✅ Test file extension: " . $file->extension());
            $this->line("✅ Test file original extension: " . $file->getClientOriginalExtension());
            
            // Test storage
            $filename = 'test_' . time() . '.jpg';
            $storedPath = $file->storeAs('vendors/temp', $filename, 'public');
            
            if ($storedPath && Storage::disk('public')->exists($storedPath)) {
                $this->line("✅ File stored successfully: " . $storedPath);
                Storage::disk('public')->delete($storedPath);
            } else {
                $this->error("❌ File storage failed");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Upload simulation failed: " . $e->getMessage());
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    private function testPermissions()
    {
        $this->section('Directory Permissions');
        
        $paths = [
            storage_path('app/public'),
            storage_path('app/public/vendors'),
            storage_path('app/public/vendors/temp'),
        ];
        
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                $writable = is_writable($path);
                $this->line(sprintf(
                    "📁 %s: %s (%s)", 
                    basename($path), 
                    $perms, 
                    $writable ? 'WRITABLE' : 'NOT WRITABLE'
                ));
            } else {
                $this->line("📁 " . basename($path) . ": NOT FOUND");
            }
        }
    }

    private function testNativePHPSpecifics()
    {
        $this->section('NativePHP Specific Issues');
        
        // Check if running in NativePHP
        $isNative = app()->environment('native') || function_exists('nativephp');
        $this->line("🔧 Running in NativePHP: " . ($isNative ? 'YES' : 'NO'));
        
        // Check for common NativePHP issues
        $this->line("🔍 Checking for known NativePHP file upload issues...");
        
        // Issue 1: Temporary directory
        $tempDir = sys_get_temp_dir();
        $this->line("📁 System temp directory: " . $tempDir);
        $this->line("📁 Temp directory writable: " . (is_writable($tempDir) ? 'YES' : 'NO'));
        
        // Issue 2: File size limits
        $maxUpload = ini_get('upload_max_filesize');
        $maxPost = ini_get('post_max_size');
        $this->line("📏 PHP upload_max_filesize: " . $maxUpload);
        $this->line("📏 PHP post_max_size: " . $maxPost);
        
        // Issue 3: Memory limits
        $memoryLimit = ini_get('memory_limit');
        $this->line("💾 PHP memory_limit: " . $memoryLimit);
        
        // Issue 4: Check if files are being uploaded at all
        $this->line("🔍 Checking recent upload activity...");
        $recentFiles = glob(storage_path('app/public/vendors/temp/*'));
        if (empty($recentFiles)) {
            $this->warn("⚠️  No files found in vendors/temp directory");
            $this->info("💡 This suggests files are not being saved during upload");
        } else {
            $this->line("✅ Found " . count($recentFiles) . " files in temp directory");
        }
    }

    private function section($title)
    {
        $this->newLine();
        $this->line("🔸 " . $title);
        $this->line(str_repeat("─", strlen($title) + 4));
    }
}
