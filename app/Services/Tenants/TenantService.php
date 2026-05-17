<?php

namespace App\Services\Tenants;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TenantService
{
    public function paginateForLandlord(User $landlord, int $perPage = 15): LengthAwarePaginator
    {
        return Tenant::query()
            ->forLandlord($landlord)
            ->with(['latestPayment'])
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(User $landlord, array $data): Tenant
    {
        return $landlord->tenants()->create($data);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->fresh();
    }

    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }
}
