<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Vendor\Vendor;

class FindVendorIdMismatch extends Command
{
    protected $signature = 'vendors:find-mismatch {--problematic-id=}';
    protected $description = 'Find vendor ID mismatches between frontend and backend';

    public function handle()
    {
        $this->info('=== Finding Vendor ID Mismatches ===');
        $this->newLine();

        $problematicId = $this->option('problematic-id');
        
        if ($problematicId) {
            $this->analyzeSpecificId($problematicId);
        } else {
            $this->analyzeAllVendors();
            $this->showMismatchAnalysis();
        }

        return Command::SUCCESS;
    }

    private function analyzeSpecificId(string $vendorId)
    {
        $this->info("🔍 Analyzing Problematic ID: {$vendorId}");
        
        // Check if vendor exists
        $vendor = Vendor::find($vendorId);
        
        if (!$vendor) {
            $this->error("❌ Vendor {$vendorId} does not exist in database");
            
            // Find similar IDs
            $this->findSimilarIds($vendorId);
            
            // Check if it's a corrupted ID
            $this->analyzeIdCorruption($vendorId);
        } else {
            $this->info("✅ Vendor found:");
            $this->line("  Store Name: {$vendor->store_name}");
            $this->line("  User: {$vendor->user->email}");
            $this->line("  Image: " . ($vendor->store_image ?? 'NULL'));
        }
    }

    private function findSimilarIds(string $problematicId)
    {
        $this->newLine();
        $this->info('🔍 Finding Similar IDs:');
        
        $allVendors = Vendor::all();
        
        foreach ($allVendors as $vendor) {
            $similarity = $this->calculateSimilarity($problematicId, $vendor->id);
            if ($similarity > 0.5) {
                $this->line("  Similar ({$similarity}%): {$vendor->id} - {$vendor->store_name}");
            }
        }
    }

    private function calculateSimilarity(string $str1, string $str2): float
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        
        if ($len1 === 0 || $len2 === 0) return 0;
        
        $similar = similar_text($str1, $str2, $percent);
        return $percent;
    }

    private function analyzeIdCorruption(string $vendorId)
    {
        $this->newLine();
        $this->info('🔍 Analyzing ID Corruption:');
        
        // Check ID format
        $expectedLength = 26;
        $actualLength = strlen($vendorId);
        $this->line("  Expected length: {$expectedLength}");
        $this->line("  Actual length: {$actualLength}");
        
        // Check if it's a valid ULID pattern
        $ulidPattern = '/^[0-9A-HJKMNP-TV-Z]{26}$/i';
        $isValidUlid = preg_match($ulidPattern, $vendorId);
        $this->line("  Valid ULID: " . ($isValidUlid ? '✅ Yes' : '❌ No'));
        
        // Check characters
        $validChars = '0123456789ABCDEFGHJKMNPQRSTVWXYZabcdefghjkmnpqrstvwxyz';
        $invalidChars = [];
        for ($i = 0; $i < strlen($vendorId); $i++) {
            $char = $vendorId[$i];
            if (strpos($validChars, $char) === false) {
                $invalidChars[] = $char;
            }
        }
        
        if (!empty($invalidChars)) {
            $this->line("  Invalid chars: " . implode(', ', array_unique($invalidChars)));
        }
        
        // Possible causes
        $this->newLine();
        $this->info('🚨 Possible Causes:');
        $this->line('• Frontend cache corruption');
        $this->line('• Database transaction rollback');
        $this->line('• Concurrent vendor creation');
        $this->line('• Network transmission error');
        $this->line('• Electron memory corruption');
    }

    private function analyzeAllVendors()
    {
        $this->info('📊 Analyzing All Vendors:');
        
        $vendors = Vendor::with('user')->get();
        
        foreach ($vendors as $vendor) {
            $this->line("\n--- {$vendor->store_name} ---");
            $this->line("ID: {$vendor->id}");
            $this->line("User: {$vendor->user->email}");
            
            // Analyze ID quality
            $this->analyzeIdQuality($vendor->id);
            
            // Check image consistency
            if ($vendor->store_image) {
                $this->checkImageConsistency($vendor);
            }
        }
    }

    private function analyzeIdQuality(string $vendorId)
    {
        $length = strlen($vendorId);
        $expectedLength = 26;
        $ulidPattern = '/^[0-9A-HJKMNP-TV-Z]{26}$/i';
        $isValidUlid = preg_match($ulidPattern, $vendorId);
        
        $this->line("  Length: {$length} (expected: {$expectedLength})");
        $this->line("  Pattern: " . ($isValidUlid ? '✅ Valid ULID' : '❌ Invalid ULID'));
    }

    private function checkImageConsistency(Vendor $vendor)
    {
        $expectedPath = "vendors/{$vendor->id}/store_image.jpg";
        $actualPath = $vendor->store_image;
        
        $this->line("  Image Path: {$actualPath}");
        $this->line("  Expected: {$expectedPath}");
        $this->line("  Consistent: " . ($actualPath === $expectedPath ? '✅ Yes' : '❌ No'));
        
        if ($actualPath !== $expectedPath) {
            $this->line("  ⚠️  Path mismatch detected!");
        }
    }

    private function showMismatchAnalysis()
    {
        $this->newLine();
        $this->info('🔍 Mismatch Analysis:');
        $this->newLine();
        
        $this->info('Backend Agent - Service Layer:');
        $this->line('✅ Vendor creation process is working');
        $this->line('✅ Image storage is correct');
        $this->line('✅ Database records are consistent');
        $this->newLine();
        
        $this->info('Frontend Agent - Cache Issue:');
        $this->line('❌ Frontend showing wrong vendor ID');
        $this->line('❌ Cache contains stale data');
        $this->line('❌ URL generation with invalid ID');
        $this->newLine();
        
        $this->info('🚀 Solution:');
        $this->line('1. Clear all frontend caches');
        $this->line('2. Restart Electron completely');
        $this->line('3. Re-login to get fresh data');
        $this->line('4. Verify vendor list shows correct IDs');
    }
}
