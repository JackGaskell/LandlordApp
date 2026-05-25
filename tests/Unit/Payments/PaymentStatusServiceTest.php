<?php

namespace Tests\Unit\Payments;

use App\Enums\PaymentStatus;
use App\Services\Payments\PaymentStatusService;
use Carbon\Carbon;
use Tests\TestCase;

class PaymentStatusServiceTest extends TestCase
{
    public function test_rent_due_today_is_due_soon_not_overdue(): void
    {
        Carbon::setTestNow('2026-05-24 15:00:00');

        $status = app(PaymentStatusService::class)->resolve(
            dueDate: Carbon::parse('2026-05-24'),
            paidAt: null,
        );

        $this->assertSame(PaymentStatus::DueSoon, $status);

        Carbon::setTestNow();
    }

    public function test_rent_due_yesterday_is_overdue(): void
    {
        Carbon::setTestNow('2026-05-24 15:00:00');

        $status = app(PaymentStatusService::class)->resolve(
            dueDate: Carbon::parse('2026-05-23'),
            paidAt: null,
        );

        $this->assertSame(PaymentStatus::Overdue, $status);

        Carbon::setTestNow();
    }
}
