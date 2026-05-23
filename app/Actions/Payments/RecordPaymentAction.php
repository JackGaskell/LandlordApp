<?php

namespace App\Actions\Payments;

use App\Enums\PaymentRecordedVia;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Payments\PaymentTrackingService;
use Illuminate\Support\Facades\DB;

/**
 * Records a rent payment and resolves its collection status.
 */
class RecordPaymentAction
{
    public function __construct(
        protected PaymentTrackingService $paymentTracking,
    ) {}

    public function execute(Tenant $tenant, array $data): PaymentHistory
    {
        return DB::transaction(function () use ($tenant, $data) {
            $data['recorded_via'] = PaymentRecordedVia::Manual;

            if (! isset($data['payment_method']) && isset($data['recorded_via'])) {
                $data['payment_method'] = $this->paymentTracking->inferPaymentMethod($data['recorded_via']);
            }

            $payment = $tenant->paymentHistories()->create($data);

            return $this->paymentTracking->sync($payment);
        });
    }
}
