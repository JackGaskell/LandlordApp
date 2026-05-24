<?php

namespace Tests\Unit\Portal;

use App\Enums\PaymentOutcome;
use App\Enums\PaymentStatus;
use App\Enums\TenantCollectionStatus;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Portal\TenantPaymentLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantPaymentLifecycleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_detects_overdue_days_for_unpaid_period(): void
    {
        $tenant = Tenant::factory()->create();
        $payment = PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->subDays(4),
            'paid_at' => null,
            'status' => PaymentStatus::Overdue,
        ]);

        $service = app(TenantPaymentLifecycleService::class);

        $this->assertSame(4, $service->daysOverdue($payment));
        $this->assertSame(TenantCollectionStatus::ActionNeeded, $service->collectionStatus($payment));
    }

    public function test_refresh_open_payment_statuses_marks_past_due_as_overdue(): void
    {
        $tenant = Tenant::factory()->create();
        $payment = PaymentHistory::factory()->for($tenant)->create([
            'due_date' => now()->subDays(2),
            'paid_at' => null,
            'status' => PaymentStatus::DueSoon,
        ]);

        app(TenantPaymentLifecycleService::class)->refreshOpenPaymentStatuses($tenant);

        $payment->refresh();

        $this->assertSame(PaymentStatus::Overdue, $payment->status);
        $this->assertSame(PaymentOutcome::Missed, $payment->payment_outcome);
    }

    public function test_builds_upcoming_rent_for_due_soon_period(): void
    {
        $tenant = Tenant::factory()->create(['rent_amount' => 900]);
        $payment = PaymentHistory::factory()->for($tenant)->create([
            'amount' => 900,
            'due_date' => now()->addDays(5),
            'paid_at' => null,
            'status' => PaymentStatus::DueSoon,
        ]);

        $upcoming = app(TenantPaymentLifecycleService::class)->buildUpcomingRent($tenant, $payment);

        $this->assertFalse($upcoming->isOverdue);
        $this->assertSame(TenantCollectionStatus::Upcoming, $upcoming->collectionStatus);
        $this->assertTrue(
            str_contains($upcoming->dueLabel, 'Due')
            || str_contains($upcoming->dueLabel, 'remaining'),
        );
    }
}
