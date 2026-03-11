<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(2),
            'image' => $this->faker->optional()->imageUrl(400, 400, 'categories'),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
            'is_visible' => true,
            'parent_id' => null,
            'meta' => [
                'title' => $this->faker->sentence(3),
                'description' => $this->faker->sentence(10),
                'keywords' => $this->faker->words(5),
            ],
        ];
    }
}
