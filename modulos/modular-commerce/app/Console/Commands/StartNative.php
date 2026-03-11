<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class StartNative extends Command
{
    protected $signature = 'native:start {--frontend}';
    protected $description = 'Start Native/Electron development environment';

    public function handle()
    {
        $this->info('=== Starting Native Development Environment ===');
        
        // Start Native backend
        $this->info('Starting Native backend...');
        $nativeProcess = new Process(['php', 'artisan', 'native:run']);
        $nativeProcess->setTimeout(null);
        $nativeProcess->start();
        
        $this->line('✅ Native backend starting on http://127.0.0.1:8100');
        
        if ($this->option('frontend')) {
            // Start frontend if requested
            $this->info('Starting frontend for Native...');
            $frontendProcess = new Process(['pnpm', 'run', 'dev:native']);
            $frontendProcess->setTimeout(null);
            $frontendProcess->start();
            
            $this->line('✅ Frontend starting on http://127.0.0.1:5173');
            
            $this->newLine();
            $this->info('=== Both Services Running ===');
            $this->line('Native Backend: http://127.0.0.1:8100');
            $this->line('Frontend Dev:   http://127.0.0.1:5173');
            $this->newLine();
            $this->info('Press Ctrl+C to stop both services');
            $this->line('Use Ctrl+Shift+D in Electron for debugging');
            
            // Wait for both processes
            $nativeProcess->wait();
            $frontendProcess->wait();
        } else {
            $this->newLine();
            $this->info('=== Native Backend Running ===');
            $this->line('Backend: http://127.0.0.1:8100');
            $this->newLine();
            $this->info('To start frontend, run: pnpm run dev:native');
            $this->line('Or run with frontend: php artisan native:start --frontend');
            
            $nativeProcess->wait();
        }
        
        return Command::SUCCESS;
    }
}
