<?php

namespace Tests\Unit\Reliability;

use App\Enums\PaymentOutcome;
use App\Enums\PaymentStatus;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Reliability\PaymentOutcomeClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentOutcomeClassifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_classifies_on_time_paid_payment(): void
    {
        $payment = PaymentHistory::factory()->for(Tenant::factory())->create([
            'due_date' => now()->subDays(5),
            'paid_at' => now()->subDays(6),
            'status' => PaymentStatus::Paid,
        ]);

        $outcome = app(PaymentOutcomeClassifier::class)->classify($payment);

        $this->assertSame(PaymentOutcome::OnTime, $outcome);
    }

    public function test_classifies_late_paid_payment(): void
    {
        $payment = PaymentHistory::factory()->for(Tenant::factory())->create([
            'due_date' => now()->subDays(10),
            'paid_at' => now()->subDays(2),
            'status' => PaymentStatus::Paid,
        ]);

        $outcome = app(PaymentOutcomeClassifier::class)->classify($payment);

        $this->assertSame(PaymentOutcome::Late, $outcome);
    }

    public function test_classifies_overdue_as_missed(): void
    {
        $payment = PaymentHistory::factory()->for(Tenant::factory())->overdue()->create();

        $outcome = app(PaymentOutcomeClassifier::class)->classify($payment);

        $this->assertSame(PaymentOutcome::Missed, $outcome);
    }
}
