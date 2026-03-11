<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user first
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@modular-commerce.com',
        ]);

        // Create vendor for products
        $vendor = \App\Modules\Vendor\Vendor::factory()->create([
            'user_id' => $adminUser->id,
            'store_name' => 'Test Store',
            'store_slug' => 'test-store',
            'status' => 'active',
        ]);

        // Create categories
        $categories = Category::factory(10)->create();

        // Create products
        Product::factory(50)->create([
            'category_id' => $categories->random()->id,
            'vendor_id' => $vendor->id,
        ]);

        // Create customers
        Customer::factory(20)->create();

        // Create orders with items
        Order::factory(30)->create()->each(function ($order) {
            OrderItem::factory(rand(1, 5))->create([
                'order_id' => $order->id,
                'product_id' => Product::inRandomOrder()->first()->id,
            ]);
        });

        // Create activities
        Activity::factory(100)->create();

        // Create test users
        User::factory(5)->create();
    }
}
