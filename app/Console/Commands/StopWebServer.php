<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StopWebServer extends Command
{
    protected $signature = 'server:stop-web';
    protected $description = 'Stop web server and keep only Native/Electron running';

    public function handle()
    {
        $this->info('=== STOPPING WEB SERVER ===');
        
        // Find and kill web servers on common ports
        $ports = [8000, 3000];
        $stopped = [];
        
        foreach ($ports as $port) {
            // Check if port is in use
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
            if ($connection) {
                fclose($connection);
                
                // Try to kill process on this port
                $this->line("Found process on port {$port}, stopping...");
                
                // macOS/Linux
                if (PHP_OS_FAMILY === 'Darwin' || PHP_OS_FAMILY === 'Linux') {
                    $command = "lsof -ti:{$port} | xargs kill -9";
                    exec($command, $output, $returnCode);
                    
                    if ($returnCode === 0) {
                        $stopped[] = $port;
                        $this->info("✅ Stopped server on port {$port}");
                    } else {
                        $this->warn("⚠️  Could not stop server on port {$port}");
                    }
                }
            } else {
                $this->line("No server on port {$port}");
            }
        }
        
        if (empty($stopped)) {
            $this->info("✅ No web servers to stop");
        }
        
        $this->newLine();
        $this->info('=== CHECKING NATIVE SERVER ===');
        
        // Check if Native/Electron server is running
        $nativePort = 8100;
        $connection = @fsockopen('127.0.0.1', $nativePort, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);
            $this->info("✅ Native server running on port {$nativePort}");
        } else {
            $this->warn("❌ Native server not found on port {$nativePort}");
            $this->line("Start Native with: php artisan native:run");
        }
        
        $this->newLine();
        $this->info('=== NEXT STEPS ===');
        $this->line('1. Use only Electron/Native app');
        $this->line('2. Clear browser cache to avoid confusion');
        $this->line('3. Use Ctrl+Shift+D in Electron for debugging');
        
        return Command::SUCCESS;
    }
}
