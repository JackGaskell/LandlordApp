<?php

namespace App\Actions\Tenants;

use App\Actions\Payments\RecordPaymentAction;
use App\Enums\PaymentVerificationStatus;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Rent\RentScheduleService;
use Illuminate\Support\Facades\DB;

/**
 * Creates a tenant and opens their first rent period for the current schedule.
 */
class CreateTenantAction
{
    public function __construct(
        protected RentScheduleService $rentSchedule,
        protected RecordPaymentAction $recordPayment,
    ) {}

    public function execute(User $landlord, array $data): Tenant
    {
        return DB::transaction(function () use ($landlord, $data) {
            $tenant = $landlord->tenants()->create($data);

            $dueDate = $this->rentSchedule->nextDueDate($tenant);

            $this->recordPayment->execute($tenant, [
                'amount' => $this->rentSchedule->scheduledAmount($tenant),
                'due_date' => $dueDate->toDateString(),
                'paid_at' => null,
                'verification_status' => PaymentVerificationStatus::Unverified->value,
            ]);

            return $tenant->fresh(['paymentHistories']);
        });
    }
}
