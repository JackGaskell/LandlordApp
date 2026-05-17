<?php

namespace Database\Factories;

use App\Enums\TenantStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->numerify('07#########'),
            'rent_amount' => fake()->randomFloat(2, 650, 2400),
            'rent_due_day' => fake()->numberBetween(1, 28),
            'status' => TenantStatus::Active,
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => TenantStatus::Inactive]);
    }
}
