<?php

namespace App\Actions\Tenants;

use App\Actions\Portal\EnableTenantPortalAction;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;

/**
 * Normalises tenant setup data and optional portal automation for create flows.
 */
class SetupTenantAction
{
    public function __construct(
        protected EnableTenantPortalAction $enablePortal,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{tenant_data: array<string, mixed>, portal_invite_url: string|null}
     */
    public function prepare(User $landlord, array $data): array
    {
        $tenantData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'] ?? null,
            'property_label' => $data['property_label'] ?? null,
            'rent_amount' => $data['rent_amount'],
            'rent_due_day' => $data['rent_due_day'],
            'status' => TenantStatus::Active,
            'notes' => $data['notes'] ?? null,
        ];

        return [
            'tenant_data' => $tenantData,
            'portal_invite_url' => null,
        ];
    }

    public function maybeEnablePortal(Tenant $tenant): ?string
    {
        if (! config('landlord.automation.auto_enable_portal_on_tenant_create', true)) {
            return null;
        }

        return $this->enablePortal->execute($tenant);
    }
}
