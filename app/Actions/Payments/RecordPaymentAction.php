<?php

namespace App\Actions\Payments;

use App\Enums\PaymentRecordedVia;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Payments\PaymentStatusService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Records a rent payment and resolves its collection status.
 */
class RecordPaymentAction
{
    public function __construct(
        protected PaymentStatusService $paymentStatus,
    ) {}

    public function execute(Tenant $tenant, array $data): PaymentHistory
    {
        return DB::transaction(function () use ($tenant, $data) {
            $paidAt = isset($data['paid_at']) ? Carbon::parse($data['paid_at']) : null;
            $dueDate = Carbon::parse($data['due_date']);
            $amount = (float) $data['amount'];

            $data['status'] = $this->paymentStatus->resolve(
                $dueDate,
                $paidAt,
                $amount,
                (float) $tenant->rent_amount,
            );

            $data['recorded_via'] = PaymentRecordedVia::Manual;

            return $tenant->paymentHistories()->create($data);
        });
    }
}
