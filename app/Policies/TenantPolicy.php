<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $this->owns($user, $tenant);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $this->owns($user, $tenant);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $this->owns($user, $tenant);
    }

    protected function owns(User $user, Tenant $tenant): bool
    {
        return $tenant->user_id === $user->id;
    }
}
