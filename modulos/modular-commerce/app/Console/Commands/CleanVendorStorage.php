<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CleanVendorStorage extends Command
{
    protected $signature = 'vendors:clean-storage {--force}';
    protected $description = 'Clean vendor storage and remove orphaned directories';

    public function handle()
    {
        $this->info('=== Cleaning Vendor Storage ===');
        $this->newLine();

        // Show current status
        $this->showStorageStatus();

        // Clean if forced
        if ($this->option('force')) {
            $this->cleanOrphanedDirectories();
            $this->cleanTempFiles();
        }

        // Show final status
        $this->showFinalStatus();

        return Command::SUCCESS;
    }

    private function showStorageStatus()
    {
        $this->info('📁 Current Storage Status:');
        
        $vendorsPath = storage_path('app/public/vendors');
        $tempPath = storage_path('app/public/vendors/temp');
        
        $this->line("Vendors Path: {$vendorsPath}");
        $this->line("Temp Path: {$tempPath}");
        $this->newLine();

        // Get all vendor directories
        $vendorDirs = glob($vendorsPath . '/*', GLOB_ONLYDIR);
        $vendorIds = array_map('basename', $vendorDirs);
        
        // Get actual vendor IDs from database
        $dbVendorIds = \App\Modules\Vendor\Vendor::pluck('id')->toArray();
        
        $this->line("Directorios encontrados: " . count($vendorIds));
        $this->line("Vendors en BD: " . count($dbVendorIds));
        $this->newLine();

        // Show orphaned directories
        $orphaned = array_diff($vendorIds, $dbVendorIds);
        if (!empty($orphaned)) {
            $this->warn("🗂️ Directorios huérfanos:");
            foreach ($orphaned as $orphan) {
                $this->line("  - {$orphan}");
            }
        } else {
            $this->info("✅ No hay directorios huérfanos");
        }

        // Show temp files
        $tempFiles = glob($tempPath . '/*');
        $this->line("Archivos temporales: " . count($tempFiles));
        
        $this->newLine();
    }

    private function cleanOrphanedDirectories()
    {
        $this->info('🧹 Cleaning orphaned directories...');
        
        $vendorsPath = storage_path('app/public/vendors');
        $vendorDirs = glob($vendorsPath . '/*', GLOB_ONLYDIR);
        $dbVendorIds = \App\Modules\Vendor\Vendor::pluck('id')->toArray();
        
        $cleaned = 0;
        foreach ($vendorDirs as $dir) {
            $dirName = basename($dir);
            
            // Skip temp directory
            if ($dirName === 'temp') continue;
            
            // Check if directory corresponds to a vendor
            if (!in_array($dirName, $dbVendorIds)) {
                $this->line("  🗑️ Eliminando: {$dirName}");
                
                try {
                    File::deleteDirectory($dir);
                    $cleaned++;
                } catch (\Exception $e) {
                    $this->error("    ❌ Error: " . $e->getMessage());
                }
            }
        }
        
        $this->info("  ✅ Directorios eliminados: {$cleaned}");
        $this->newLine();
    }

    private function cleanTempFiles()
    {
        $this->info('🧹 Cleaning temporary files...');
        
        $tempPath = storage_path('app/public/vendors/temp');
        $tempFiles = glob($tempPath . '/*');
        
        $cleaned = 0;
        foreach ($tempFiles as $file) {
            $this->line("  🗑️ Eliminando: " . basename($file));
            
            try {
                unlink($file);
                $cleaned++;
            } catch (\Exception $e) {
                $this->error("    ❌ Error: " . $e->getMessage());
            }
        }
        
        $this->info("  ✅ Archivos eliminados: {$cleaned}");
        $this->newLine();
    }

    private function showFinalStatus()
    {
        $this->info('📊 Final Status:');
        
        // Show AMAF vendor specifically
        $amafVendor = \App\Modules\Vendor\Vendor::whereHas('user', function($q) {
            $q->where('email', 'amaf2511@gmail.com');
        })->with('user')->first();
        
        if ($amafVendor) {
            $this->line("✅ Vendor de AMAF:");
            $this->line("  Nombre: {$amafVendor->store_name}");
            $this->line("  ID: {$amafVendor->id}");
            $this->line("  Email: {$amafVendor->user->email}");
            $this->line("  Imagen: " . ($amafVendor->store_image ? '✅ SÍ' : '❌ NO'));
            
            if ($amafVendor->store_image) {
                $imageUrl = 'http://127.0.0.1:8100/storage/' . $amafVendor->store_image;
                $this->line("  URL: {$imageUrl}");
                
                $fullPath = storage_path('app/public/' . $amafVendor->store_image);
                $this->line("  Archivo: " . (file_exists($fullPath) ? '✅ Existe' : '❌ No existe'));
            }
        } else {
            $this->error("❌ No se encontró vendor de AMAF");
        }
        
        $this->newLine();
        $this->info('🚀 Resumen:');
        $this->line('• Base de datos: SQLite (única)');
        $this->line('• Directorio storage: Unificado');
        $this->line('• Vendor AMAF: ' . ($amafVendor ? '✅ Configurado' : '❌ No encontrado'));
        $this->line('• Imágenes: ' . ($amafVendor && $amafVendor->store_image ? '✅ Listas' : '❌ Faltantes'));
    }
}
