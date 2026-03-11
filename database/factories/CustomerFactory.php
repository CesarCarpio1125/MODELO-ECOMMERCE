<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'date_of_birth' => $this->faker->optional()->dateTimeBetween('-60 years', '-18 years'),
            'gender' => $this->faker->optional()->randomElement(['male', 'female', 'other']),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'total_spent' => $this->faker->randomFloat(2, 0, 5000),
            'orders_count' => $this->faker->numberBetween(0, 50),
            'last_order_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'preferences' => [
                'newsletter' => $this->faker->boolean(),
                'sms_notifications' => $this->faker->boolean(),
                'language' => $this->faker->randomElement(['en', 'es', 'fr']),
                'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            ],
        ];
    }
}
