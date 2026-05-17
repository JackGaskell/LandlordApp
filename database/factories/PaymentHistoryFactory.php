<?php

namespace Database\Factories;

use App\Enums\PaymentRecordedVia;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentHistory>
 */
class PaymentHistoryFactory extends Factory
{
    public function definition(): array
    {
        $dueDate = fake()->dateTimeBetween('-2 months', '+1 month');

        return [
            'tenant_id' => Tenant::factory(),
            'amount' => fake()->randomFloat(2, 650, 2400),
            'due_date' => $dueDate,
            'paid_at' => null,
            'status' => PaymentStatus::DueSoon,
            'verification_status' => PaymentVerificationStatus::Unverified,
            'recorded_via' => PaymentRecordedVia::Manual,
        ];
    }

    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            $dueDate = $attributes['due_date'] ?? now()->subDays(5);

            return [
                'due_date' => $dueDate,
                'paid_at' => now()->subDays(2),
                'status' => PaymentStatus::Paid,
                'verification_status' => PaymentVerificationStatus::Verified,
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => now()->subDays(fake()->numberBetween(3, 21)),
            'paid_at' => null,
            'status' => PaymentStatus::Overdue,
            'verification_status' => PaymentVerificationStatus::Unverified,
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn () => [
            'due_date' => now()->addDays(fake()->numberBetween(1, 5)),
            'paid_at' => null,
            'status' => PaymentStatus::DueSoon,
            'verification_status' => PaymentVerificationStatus::Unverified,
        ]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(fn () => [
            'due_date' => now()->subDays(2),
            'paid_at' => now()->subDay(),
            'status' => PaymentStatus::PartiallyPaid,
            'verification_status' => PaymentVerificationStatus::Unverified,
        ]);
    }
}
