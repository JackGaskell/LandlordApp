<?php

namespace App\Policies;

use App\Models\PaymentProof;
use App\Models\Tenant;
use App\Models\User;

class PaymentProofPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function viewAnyTenant(Tenant $tenant): bool
    {
        return $tenant->hasPortalAccess();
    }

    public function view(User|Tenant $actor, PaymentProof $proof): bool
    {
        if ($actor instanceof Tenant) {
            return $proof->tenant_id === $actor->id;
        }

        return $proof->tenant->user_id === $actor->id;
    }

    public function create(Tenant $tenant): bool
    {
        return $tenant->hasPortalAccess();
    }

    public function review(User $user, PaymentProof $proof): bool
    {
        return $proof->tenant->user_id === $user->id && $proof->isPending();
    }

    public function download(User|Tenant $actor, PaymentProof $proof): bool
    {
        return $this->view($actor, $proof);
    }
}
