<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Rent\RentPeriodAutomationService;
use Illuminate\Support\Facades\DB;

/**
 * Creates a tenant; rent periods and reminders are handled automatically from there.
 */
class CreateTenantAction
{
    public function __construct(
        protected SetupTenantAction $setupTenant,
        protected RentPeriodAutomationService $rentPeriods,
    ) {}

    /**
     * @return array{tenant: Tenant, portal_invite_url: string|null}
     */
    public function execute(User $landlord, array $data): array
    {
        return DB::transaction(function () use ($landlord, $data) {
            $prepared = $this->setupTenant->prepare($landlord, $data);

            $tenant = $landlord->tenants()->create($prepared['tenant_data']);

            $this->rentPeriods->maintainTenantSchedule($tenant);

            $inviteUrl = $this->setupTenant->maybeEnablePortal($tenant);

            return [
                'tenant' => $tenant->fresh(['paymentHistories']),
                'portal_invite_url' => $inviteUrl,
            ];
        });
    }
}
