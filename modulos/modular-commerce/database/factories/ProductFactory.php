<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(3),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'compare_price' => $this->faker->optional(0.3)->randomFloat(2, 50, 600),
            'cost_price' => $this->faker->randomFloat(2, 5, 200),
            'sku' => $this->faker->unique()->bothify('#####'),
            'barcode' => $this->faker->optional()->ean13(),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'min_stock_level' => $this->faker->numberBetween(0, 10),
            'track_stock' => true,
            'is_active' => true,
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'images' => [
                $this->faker->imageUrl(400, 400, 'products'),
                $this->faker->imageUrl(400, 400, 'products'),
            ],
            'attributes' => [
                'color' => $this->faker->colorName(),
                'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool']),
            ],
            'category_id' => Category::factory(),
            'created_by' => User::factory(),
        ];
    }
}
