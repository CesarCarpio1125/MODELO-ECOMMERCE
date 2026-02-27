<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activityTypes = [
            'order_created' => [
                'description' => 'created a new order',
                'icon' => 'shopping-cart',
                'color' => 'blue',
            ],
            'product_updated' => [
                'description' => 'updated a product',
                'icon' => 'edit',
                'color' => 'purple',
            ],
            'customer_registered' => [
                'description' => 'registered as a new customer',
                'icon' => 'user-plus',
                'color' => 'green',
            ],
            'order_shipped' => [
                'description' => 'shipped an order',
                'icon' => 'truck',
                'color' => 'orange',
            ],
        ];

        $type = $this->faker->randomElement(array_keys($activityTypes));
        $activityData = $activityTypes[$type];

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'description' => $activityData['description'],
            'properties' => [
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'details' => $this->faker->sentence(),
            ],
            'icon' => $activityData['icon'],
            'color' => $activityData['color'],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}
