<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Activity;
use App\Modules\Vendor\Vendor;
use App\Modules\Vendor\Product;

class MigrateFromDocker extends Command
{
    protected $signature = 'migrate:from-docker {--dry-run}';
    protected $description = 'Migrate data from Docker MySQL to Native SQLite';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No data will be modified');
        } else {
            $this->warn('⚠️  LIVE MIGRATION - Data will be modified');
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Migration cancelled');
                return Command::SUCCESS;
            }
        }

        $this->info('=== Migrating from Docker MySQL to Native SQLite ===');
        $this->newLine();

        // Connect to Docker database
        $this->connectToDockerDatabase();

        try {
            // Analyze Docker database
            $this->analyzeDockerDatabase();

            if ($isDryRun) {
                $this->showMigrationPlan();
            } else {
                $this->performMigration();
            }

        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function connectToDockerDatabase()
    {
        $this->info('🔌 Connecting to Docker database...');
        
        // Docker MySQL connection details
        config(['database.connections.docker_mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'modular_commerce',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]]);

        // Test connection
        try {
            DB::connection('docker_mysql')->getPdo();
            $this->info('✅ Connected to Docker MySQL database');
        } catch (\Exception $e) {
            $this->error('❌ Failed to connect to Docker database: ' . $e->getMessage());
            throw $e;
        }
    }

    private function analyzeDockerDatabase()
    {
        $this->info('📊 Analyzing Docker database...');
        
        $dockerDB = DB::connection('docker_mysql');
        
        // Get table counts
        $tables = [
            'users' => 'Users',
            'vendors' => 'Vendors', 
            'products' => 'Products',
            'orders' => 'Orders',
            'customers' => 'Customers',
            'categories' => 'Categories',
            'activities' => 'Activities',
        ];

        foreach ($tables as $table => $label) {
            try {
                $count = $dockerDB->table($table)->count();
                $this->line("  {$label}: {$count} records");
            } catch (\Exception $e) {
                $this->line("  {$label}: Table not found or error");
            }
        }

        $this->newLine();
    }

    private function showMigrationPlan()
    {
        $this->info('📋 Migration Plan:');
        $this->newLine();

        $this->line('1. Backup current Native database');
        $this->line('2. Migrate Users (preserve existing Native users)');
        $this->line('3. Migrate Vendors');
        $this->line('4. Migrate Products');
        $this->line('5. Migrate Orders');
        $this->line('6. Migrate Customers');
        $this->line('7. Migrate Categories');
        $this->line('8. Migrate Activities');
        $this->newLine();

        $this->info('Run without --dry-run to execute migration');
    }

    private function performMigration()
    {
        $this->info('🚀 Starting migration...');
        $dockerDB = DB::connection('docker_mysql');

        // Backup current database
        $this->backupCurrentDatabase();

        // Migrate in order of dependencies
        $this->migrateUsers($dockerDB);
        $this->migrateVendors($dockerDB);
        $this->migrateProducts($dockerDB);
        $this->migrateOrders($dockerDB);
        $this->migrateCustomers($dockerDB);
        $this->migrateCategories($dockerDB);
        $this->migrateActivities($dockerDB);

        $this->newLine();
        $this->info('✅ Migration completed successfully!');
    }

    private function backupCurrentDatabase()
    {
        $this->info('💾 Backing up current database...');
        
        $backupPath = database_path('backups/native_backup_' . date('Y-m-d_H-i-s') . '.sqlite');
        
        if (!is_dir(database_path('backups'))) {
            mkdir(database_path('backups'), 0755, true);
        }
        
        copy(database_path('database.sqlite'), $backupPath);
        $this->info("✅ Backup saved to: {$backupPath}");
    }

    private function migrateUsers($dockerDB)
    {
        $this->info('👥 Migrating Users...');
        
        $dockerUsers = $dockerDB->table('users')->get();
        $migrated = 0;
        $skipped = 0;

        foreach ($dockerUsers as $dockerUser) {
            // Check if user already exists in Native
            $existingUser = User::where('email', $dockerUser->email)->first();
            
            if ($existingUser) {
                $skipped++;
                continue;
            }

            // Create new user
            User::create([
                'name' => $dockerUser->name,
                'email' => $dockerUser->email,
                'password' => $dockerUser->password,
                'email_verified_at' => $dockerUser->email_verified_at,
                'role' => $dockerUser->role ?? 'customer',
                'created_at' => $dockerUser->created_at,
                'updated_at' => $dockerUser->updated_at,
            ]);
            
            $migrated++;
        }

        $this->line("  ✅ Users migrated: {$migrated}, skipped: {$skipped}");
    }

    private function migrateVendors($dockerDB)
    {
        $this->info('🏪 Migrating Vendors...');
        
        $dockerVendors = $dockerDB->table('vendors')->get();
        $migrated = 0;

        foreach ($dockerVendors as $dockerVendor) {
            // Find corresponding user
            $user = User::where('email', $dockerVendor->user_email ?? '')->first();
            
            if (!$user) {
                continue;
            }

            Vendor::create([
                'user_id' => $user->id,
                'store_name' => $dockerVendor->store_name,
                'store_slug' => $dockerVendor->store_slug,
                'description' => $dockerVendor->description,
                'status' => $dockerVendor->status ?? 'active',
                'store_image' => $dockerVendor->store_image,
                'created_at' => $dockerVendor->created_at,
                'updated_at' => $dockerVendor->updated_at,
            ]);
            
            $migrated++;
        }

        $this->line("  ✅ Vendors migrated: {$migrated}");
    }

    private function migrateProducts($dockerDB)
    {
        $this->info('📦 Migrating Products...');
        
        $dockerProducts = $dockerDB->table('products')->get();
        $migrated = 0;

        foreach ($dockerProducts as $dockerProduct) {
            Product::create([
                'name' => $dockerProduct->name,
                'slug' => $dockerProduct->slug,
                'description' => $dockerProduct->description,
                'price' => $dockerProduct->price,
                'sku' => $dockerProduct->sku,
                'stock_quantity' => $dockerProduct->stock_quantity,
                'is_active' => $dockerProduct->is_active,
                'vendor_id' => $dockerProduct->vendor_id,
                'category_id' => $dockerProduct->category_id,
                'featured_image' => $dockerProduct->featured_image,
                'images' => $dockerProduct->images,
                'created_at' => $dockerProduct->created_at,
                'updated_at' => $dockerProduct->updated_at,
            ]);
            
            $migrated++;
        }

        $this->line("  ✅ Products migrated: {$migrated}");
    }

    private function migrateOrders($dockerDB)
    {
        $this->info('🛒 Migrating Orders...');
        
        $dockerOrders = $dockerDB->table('orders')->get();
        $migrated = 0;

        foreach ($dockerOrders as $dockerOrder) {
            Order::create([
                'user_id' => $dockerOrder->user_id,
                'customer_id' => $dockerOrder->customer_id,
                'order_number' => $dockerOrder->order_number,
                'total_amount' => $dockerOrder->total_amount,
                'status' => $dockerOrder->status,
                'payment_status' => $dockerOrder->payment_status,
                'created_at' => $dockerOrder->created_at,
                'updated_at' => $dockerOrder->updated_at,
            ]);
            
            $migrated++;
        }

        $this->line("  ✅ Orders migrated: {$migrated}");
    }

    private function migrateCustomers($dockerDB)
    {
        $this->info('👤 Migrating Customers...');
        
        $dockerCustomers = $dockerDB->table('customers')->get();
        $migrated = 0;

        foreach ($dockerCustomers as $dockerCustomer) {
            Customer::create([
                'name' => $dockerCustomer->name,
                'email' => $dockerCustomer->email,
                'phone' => $dockerCustomer->phone ?? null,
                'address' => $dockerCustomer->address ?? null,
                'created_at' => $dockerCustomer->created_at,
                'updated_at' => $dockerCustomer->updated_at,
            ]);
            
            $migrated++;
        }

        $this->line("  ✅ Customers migrated: {$migrated}");
    }

    private function migrateCategories($dockerDB)
    {
        $this->info('📂 Migrating Categories...');
        
        $dockerCategories = $dockerDB->table('categories')->get();
        $migrated = 0;

        foreach ($dockerCategories as $dockerCategory) {
            Category::create([
                'name' => $dockerCategory->name,
                'slug' => $dockerCategory->slug,
                'description' => $dockerCategory->description,
                'is_active' => $dockerCategory->is_active ?? true,
                'created_at' => $dockerCategory->created_at,
                'updated_at' => $dockerCategory->updated_at,
            ]);
            
            $migrated++;
        }

        $this->line("  ✅ Categories migrated: {$migrated}");
    }

    private function migrateActivities($dockerDB)
    {
        $this->info('📝 Migrating Activities...');
        
        $dockerActivities = $dockerDB->table('activities')->get();
        $migrated = 0;

        foreach ($dockerActivities as $dockerActivity) {
            Activity::create([
                'user_id' => $dockerActivity->user_id,
                'type' => $dockerActivity->type,
                'description' => $dockerActivity->description,
                'created_at' => $dockerActivity->created_at,
                'updated_at' => $dockerActivity->updated_at,
            ]);
            
            $migrated++;
        }

        $this->line("  ✅ Activities migrated: {$migrated}");
    }
}
