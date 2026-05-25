<?php

namespace App\Services\Rent;

use App\Actions\Payments\RecordPaymentAction;
use App\Enums\PaymentVerificationStatus;
use App\Enums\TenantStatus;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Payments\PaymentTrackingService;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Keeps rent periods and collection status in sync without landlord data entry.
 */
class RentPeriodAutomationService
{
    public function __construct(
        protected RentScheduleService $rentSchedule,
        protected RecordPaymentAction $recordPayment,
        protected PaymentTrackingService $paymentTracking,
    ) {}

    public function runForLandlord(User $landlord): int
    {
        $processed = 0;

        Tenant::query()
            ->forLandlord($landlord)
            ->where('status', TenantStatus::Active)
            ->orderBy('id')
            ->each(function (Tenant $tenant) use (&$processed) {
                if ($this->maintainTenantSchedule($tenant)) {
                    $processed++;
                }
            });

        return $processed;
    }

    public function runForAllLandlords(): int
    {
        $processed = 0;

        User::query()
            ->orderBy('id')
            ->each(function (User $landlord) use (&$processed) {
                $processed += $this->runForLandlord($landlord);
            });

        return $processed;
    }

    /**
     * Sync open periods and ensure exactly one upcoming/open rent period exists for active tenants.
     */
    public function maintainTenantSchedule(Tenant $tenant): bool
    {
        if ($tenant->status !== TenantStatus::Active) {
            return false;
        }

        $this->paymentTracking->syncOutstandingPaymentsForTenant($tenant);

        if ($tenant->paymentHistories()->outstanding()->exists()) {
            return true;
        }

        $dueDate = $this->nextOpenPeriodDueDate($tenant);

        if ($tenant->paymentHistories()->whereDate('due_date', $dueDate->toDateString())->exists()) {
            return true;
        }

        $this->recordPayment->execute($tenant, [
            'amount' => $this->rentSchedule->scheduledAmount($tenant),
            'due_date' => $dueDate->toDateString(),
            'paid_at' => null,
            'verification_status' => PaymentVerificationStatus::Unverified->value,
        ]);

        return true;
    }

    public function advanceAfterPeriodSettled(PaymentHistory $payment): void
    {
        $payment->refresh();

        if (! $payment->status->isSettled()) {
            return;
        }

        $tenant = $payment->tenant;

        if ($tenant === null || $tenant->status !== TenantStatus::Active) {
            return;
        }

        $this->maintainTenantSchedule($tenant->fresh());
    }

    protected function nextOpenPeriodDueDate(Tenant $tenant): Carbon
    {
        $latest = $tenant->paymentHistories()
            ->orderByDesc('due_date')
            ->first();

        if ($latest === null) {
            return $this->rentSchedule->nextDueDate($tenant);
        }

        if (! $latest->status->isSettled()) {
            return Carbon::parse($latest->due_date)->startOfDay();
        }

        $following = Carbon::parse($latest->due_date)->startOfDay()->addMonthNoOverflow();

        return $this->rentSchedule->dueDateForMonth($tenant, $following->year, $following->month);
    }
}
