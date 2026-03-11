<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Vendor\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Vendor\Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'store_name' => $this->faker->company(),
            'store_slug' => $this->faker->slug(),
            'description' => $this->faker->sentence(10),
            'status' => 'pending',
        ];
    }
}
