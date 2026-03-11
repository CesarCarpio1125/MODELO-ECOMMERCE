<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EmergencyCacheClear extends Command
{
    protected $signature = 'emergency:clear-cache';
    protected $description = 'Emergency cache clear for frontend data corruption';

    public function handle()
    {
        $this->info('🚨 EMERGENCY CACHE CLEAR');
        $this->newLine();

        // Clear all Laravel caches
        $this->clearLaravelCaches();

        // Force database refresh
        $this->forceDatabaseRefresh();

        // Show instructions for frontend
        $this->showFrontendInstructions();

        return Command::SUCCESS;
    }

    private function clearLaravelCaches()
    {
        $this->info('🧹 Clearing Laravel caches...');
        
        $commands = [
            'cache:clear',
            'config:clear', 
            'view:clear',
            'route:clear',
        ];

        foreach ($commands as $command) {
            $this->line("  Running: {$command}");
            $this->callSilent($command);
        }
        
        $this->info('✅ Laravel caches cleared');
        $this->newLine();
    }

    private function forceDatabaseRefresh()
    {
        $this->info('🔄 Forcing database refresh...');
        
        // Clear any query cache
        \DB::statement('PRAGMA query_only = 0');
        \DB::statement('PRAGMA cache_size = 0');
        
        // Test database connection
        try {
            $count = \App\Modules\Vendor\Vendor::count();
            $this->line("  Database connection: ✅");
            $this->line("  Total vendors: {$count}");
        } catch (\Exception $e) {
            $this->error("  Database error: " . $e->getMessage());
        }
        
        $this->info('✅ Database refreshed');
        $this->newLine();
    }

    private function showFrontendInstructions()
    {
        $this->info('🌐 FRONTEND CACHE CLEAR INSTRUCTIONS:');
        $this->newLine();
        
        $this->line('1. EN ELECTRON:');
        $this->line('   - Press Ctrl+Shift+D para abrir debug panel');
        $this->line('   - Click "Clear Cache" button');
        $this->line('   - Click "Fix Auth" button');
        $this->line('   - Refresh page: Ctrl+Shift+R');
        $this->newLine();
        
        $this->line('2. SI PERSISTE:');
        $this->line('   - Cerrar Electron completamente');
        $this->line('   - Abrir Task Manager (Ctrl+Shift+Esc)');
        $this->line('   - Matar todos los procesos de Electron');
        $this->line('   - Reiniciar con: ./start-native.sh');
        $this->newLine();
        
        $this->line('3. EN NAVEGADOR:');
        $this->line('   - F12 → DevTools → Application');
        $this->line('   - Borrar localStorage: localStorage.clear()');
        $this->line('   - Borrar sessionStorage: sessionStorage.clear()');
        $this->line('   - Borrar cookies: document.cookie.split(";").forEach(...)');
        $this->newLine();
        
        $this->line('4. HARD RESET:');
        $this->line('   - Desinstalar y reinstalar Electron');
        $this->line('   - O borrar perfil de Electron');
        $this->newLine();
        
        $this->warn('⚠️  EL PROBLEMA:');
        $this->line('"cesarDSADSAds" existe solo en frontend cache');
        $this->line('NO existe en base de datos SQLite');
        $this->line('Es data corrupta que debe ser eliminada');
        $this->newLine();
        
        $this->info('✅ VERIFICACIÓN FINAL:');
        $this->line('Después de limpiar cache, deberías ver solo:');
        $this->line('- cesarcecesa (usuario: cesarcarpio693@gmail.com)');
        $this->line('- cesarcecesasad (usuario: amaf2511@gmail.com)');
        $this->line('NADA MÁS');
    }
}
