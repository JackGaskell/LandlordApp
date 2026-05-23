<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentRecordedVia;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Reliability\LatePaymentDetector;
use App\Services\Reliability\PaymentOutcomeClassifier;
use App\Services\Reliability\TenantReliabilityProfileService;

/**
 * Single entry point for syncing payment status, behavioural outcomes, and tenant reliability cache.
 */
class PaymentTrackingService
{
    public function __construct(
        protected PaymentStatusService $paymentStatus,
        protected PaymentOutcomeClassifier $outcomes,
        protected LatePaymentDetector $latePayments,
        protected TenantReliabilityProfileService $reliability,
    ) {}

    public function sync(PaymentHistory $payment): PaymentHistory
    {
        $payment = $this->syncPaymentRecord($payment);
        $this->refreshTenantReliability($payment->tenant);

        return $payment;
    }

    /**
     * Sync all open rent periods for a tenant, then recalculate reliability once.
     */
    public function syncOutstandingPaymentsForTenant(Tenant $tenant): void
    {
        $payments = $tenant->paymentHistories()->whereNull('paid_at')->get();

        foreach ($payments as $payment) {
            $this->syncPaymentRecord($payment);
        }

        if ($payments->isNotEmpty()) {
            $this->refreshTenantReliability($tenant);
        }
    }

    public function syncPaymentRecord(PaymentHistory $payment): PaymentHistory
    {
        return PaymentHistory::withoutEvents(function () use ($payment) {
            $this->paymentStatus->sync($payment);
            $payment->refresh();

            if (! $payment->payment_method && $payment->recorded_via) {
                $payment->payment_method = PaymentMethod::fromRecordedVia($payment->recorded_via);
            }

            $outcome = $this->outcomes->classify($payment);
            $daysLate = $this->latePayments->daysLate($payment);

            $payment->forceFill([
                'payment_outcome' => $outcome,
                'days_late' => $daysLate,
                'payment_method' => $payment->payment_method,
            ])->save();

            return $payment->fresh();
        });
    }

    public function refreshTenantReliability(Tenant|int|null $tenant): void
    {
        if ($tenant === null) {
            return;
        }

        $tenant = $tenant instanceof Tenant
            ? $tenant
            : Tenant::query()->find($tenant);

        if (! $tenant) {
            return;
        }

        $profile = $this->reliability->profileFromPayments($tenant);
        $this->reliability->persistCache($tenant, $profile);
    }

    public function inferPaymentMethod(PaymentRecordedVia $via): PaymentMethod
    {
        return PaymentMethod::fromRecordedVia($via);
    }
}
