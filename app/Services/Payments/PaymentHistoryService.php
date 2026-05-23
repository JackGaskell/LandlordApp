<?php

namespace App\Services\Payments;

use App\Models\PaymentHistory;
use App\Models\Tenant;
use Illuminate\Support\Collection;

/**
 * Read-focused payment queries. Writes go through Actions.
 */
class PaymentHistoryService
{
    public function __construct(
        protected PaymentTrackingService $paymentTracking,
    ) {}

    public function listForTenant(Tenant $tenant): Collection
    {
        return $tenant->paymentHistories()
            ->orderByDesc('due_date')
            ->get();
    }

    public function update(PaymentHistory $payment, array $data): PaymentHistory
    {
        $payment->update($data);

        $shouldRecalculate = collect(['due_date', 'paid_at', 'amount', 'status', 'payment_method', 'notes'])
            ->contains(fn (string $key) => array_key_exists($key, $data));

        if ($shouldRecalculate) {
            return $this->paymentTracking->sync($payment);
        }

        return $payment->fresh();
    }

    public function delete(PaymentHistory $payment): void
    {
        $tenantId = $payment->tenant_id;
        $payment->delete();
        $this->paymentTracking->refreshTenantReliability($tenantId);
    }
}
