<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create new products table with ULID primary key
        Schema::create('products_new', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_level')->default(0);
            $table->boolean('track_stock')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('images')->nullable();
            $table->json('attributes')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->ulid('vendor_id')->nullable();
            $table->ulid('created_by');
            $table->string('status')->default('draft');
            $table->json('tags')->nullable();
            $table->json('dimensions')->nullable();
            $table->string('featured_image')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['is_active', 'created_at']);
            $table->index(['category_id', 'is_active']);
        });

        // Migrate data from old table to new table
        DB::statement('INSERT INTO products_new (name, slug, description, price, stock_quantity, sku, weight, dimensions, status, category_id, tags, created_by, vendor_id, featured_image, created_at, updated_at)
            SELECT name, slug, description, price, stock_quantity, sku, weight, dimensions, status, category_id, tags, created_by, vendor_id, featured_image, created_at, updated_at
            FROM products');

        // Drop old table and rename new table
        Schema::dropIfExists('products');
        Schema::rename('products_new', 'products');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
