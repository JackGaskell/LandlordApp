<?php

namespace Tests\Unit\Reliability;

use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Payments\PaymentTrackingService;
use App\Services\Reliability\PaymentStreakCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentStreakCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_trailing_on_time_streak(): void
    {
        $tenant = Tenant::factory()->create();
        $tracking = app(PaymentTrackingService::class);

        foreach ([3, 2, 1] as $monthsAgo) {
            $payment = PaymentHistory::factory()->paid()->for($tenant)->create([
                'due_date' => now()->subMonths($monthsAgo)->day(5),
                'paid_at' => now()->subMonths($monthsAgo)->day(4),
            ]);
            $tracking->sync($payment);
        }

        $late = PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->subMonths(4)->day(5),
            'paid_at' => now()->subMonths(4)->day(12),
            'status' => \App\Enums\PaymentStatus::Paid,
        ]);
        $tracking->sync($late);

        $payments = $tenant->paymentHistories()->orderByDesc('due_date')->get();
        $streak = app(PaymentStreakCalculator::class)->calculate($payments);

        $this->assertSame(3, $streak['current'], 'Streak should stop at the late payment');
        $this->assertGreaterThanOrEqual(3, $streak['best']);
    }
}
