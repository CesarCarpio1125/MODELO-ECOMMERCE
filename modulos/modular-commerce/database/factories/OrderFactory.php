<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'order_number' => 'ORD-'.$this->faker->unique()->numberBetween(100000, 999999),
            'total_amount' => $this->faker->randomFloat(2, 50, 1000),
            'tax_amount' => $this->faker->randomFloat(2, 5, 100),
            'shipping_amount' => $this->faker->randomFloat(2, 0, 50),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer']),
            'notes' => $this->faker->optional()->sentence(),
            'shipped_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'delivered_at' => $this->faker->optional(0.2)->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
