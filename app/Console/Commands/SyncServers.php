<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncServers extends Command
{
    protected $signature = 'servers:sync {port=8000}';
    protected $description = 'Check and sync servers to use the same port';

    public function handle()
    {
        $port = $this->argument('port');
        $host = '127.0.0.1';
        
        $this->info("=== SERVER SYNC TO PORT {$port} ===");
        
        // Check if target port is available
        $connection = @fsockopen($host, $port, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);
            $this->info("✅ Server already running on port {$port}");
        } else {
            $this->warn("❌ No server on port {$port}");
            $this->line("Start server with: php artisan serve --port={$port}");
            return Command::FAILURE;
        }
        
        // Test connectivity
        try {
            $response = Http::timeout(5)->get("http://{$host}:{$port}/api/public/status");
            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ Server responding correctly");
                $this->line("Environment: {$data['environment']}");
                $this->line("Native: " . ($data['is_native'] ? 'Yes' : 'No'));
                $this->line("Base URL: {$data['base_url']}");
            } else {
                $this->error("❌ Server not responding correctly");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error connecting to server: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        $this->newLine();
        $this->info("=== ELECTRON INSTRUCTIONS ===");
        $this->line("1. Make sure Electron is configured to use port {$port}");
        $this->line("2. Restart Electron application");
        $this->line("3. Use Ctrl+Shift+D → 'Fix Auth' if needed");
        $this->line("4. Verify both web and Electron show same user data");
        
        return Command::SUCCESS;
    }
}
