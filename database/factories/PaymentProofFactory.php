<?php

namespace Database\Factories;

use App\Enums\PaymentProofStatus;
use App\Models\PaymentHistory;
use App\Models\PaymentProof;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentProof>
 */
class PaymentProofFactory extends Factory
{
    protected $model = PaymentProof::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'payment_history_id' => PaymentHistory::factory(),
            'status' => PaymentProofStatus::Pending,
            'disk' => 'local',
            'path' => 'payment-proofs/demo/receipt.pdf',
            'original_filename' => 'bank-transfer.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'tenant_note' => fake()->optional()->sentence(),
            'tenant_marked_paid' => true,
            'claimed_paid_at' => now(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => PaymentProofStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => PaymentProofStatus::Rejected,
            'reviewed_at' => now(),
        ]);
    }
}
