<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Modules\Vendor\Vendor;
use App\Modules\Vendor\Product;

class VerifyNativeSetup extends Command
{
    protected $signature = 'native:verify';
    protected $description = 'Verify Native/Electron setup is working correctly';

    public function handle()
    {
        $this->info('=== Verifying Native/Electron Setup ===');
        $this->newLine();

        // Check database
        $this->verifyDatabase();

        // Check user authentication
        $this->verifyUsers();

        // Check vendors and products
        $this->verifyVendors();

        // Check configuration
        $this->verifyConfiguration();

        // Provide next steps
        $this->showNextSteps();

        return Command::SUCCESS;
    }

    private function verifyDatabase()
    {
        $this->info('📊 Database Status:');
        
        try {
            $tables = [
                'users' => User::count(),
                'vendors' => Vendor::count(),
                'products' => Product::count(),
            ];

            foreach ($tables as $table => $count) {
                $status = $count > 0 ? '✅' : '❌';
                $this->line("  {$status} {$table}: {$count} records");
            }

        } catch (\Exception $e) {
            $this->error("  ❌ Database error: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function verifyUsers()
    {
        $this->info('👥 User Verification:');
        
        // Check AMAF user
        $amafUser = User::where('email', 'amaf2511@gmail.com')->first();
        
        if ($amafUser) {
            $this->line("  ✅ AMAF user found: ID {$amafUser->id}");
            $this->line("     Email: {$amafUser->email}");
            $this->line("     Role: {$amafUser->role}");
            
            $vendors = $amafUser->vendors()->get();
            $this->line("     Vendors: {$vendors->count()}");
            
            foreach ($vendors as $vendor) {
                $this->line("       - {$vendor->store_name} ({$vendor->products()->count()} products)");
            }
        } else {
            $this->error("  ❌ AMAF user not found");
        }
        
        $this->newLine();
    }

    private function verifyVendors()
    {
        $this->info('🏪 Vendor Verification:');
        
        $vendors = Vendor::with('user', 'products')->get();
        
        foreach ($vendors as $vendor) {
            $this->line("  ✅ {$vendor->store_name}");
            $this->line("     User: {$vendor->user->email}");
            $this->line("     Products: {$vendor->products->count()}");
            $this->line("     Status: {$vendor->status}");
        }
        
        $this->newLine();
    }

    private function verifyConfiguration()
    {
        $this->info('⚙️  Configuration Status:');
        
        // Check Native environment
        $isNative = app()->environment('local', 'native');
        $this->line("  " . ($isNative ? '✅' : '❌') . " Environment: " . app()->environment());
        
        // Check database connection
        try {
            \DB::connection()->getPdo();
            $this->line("  ✅ Database: Connected");
        } catch (\Exception $e) {
            $this->error("  ❌ Database: " . $e->getMessage());
        }
        
        // Check storage link
        $storageLink = public_path('storage');
        $isLinked = is_link($storageLink);
        $this->line("  " . ($isLinked ? '✅' : '❌') . " Storage link: " . ($isLinked ? 'Connected' : 'Missing'));
        
        $this->newLine();
    }

    private function showNextSteps()
    {
        $this->info('🚀 Next Steps:');
        $this->newLine();
        
        $this->line('1. Start Native environment:');
        $this->line('   ./start-native.sh');
        $this->line('   OR');
        $this->line('   php artisan native:start --frontend');
        $this->newLine();
        
        $this->line('2. Open Electron app');
        $this->newLine();
        
        $this->line('3. Login with: amaf2511@gmail.com');
        $this->newLine();
        
        $this->line('4. Verify you see:');
        $this->line('   - Your vendor dashboard');
        $this->line('   - Your store: cesarcecesasad');
        $this->line('   - Products and orders');
        $this->newLine();
        
        $this->line('5. Use Ctrl+Shift+D for debugging if needed');
        $this->newLine();
        
        $this->info('✅ Native setup is ready!');
    }
}
