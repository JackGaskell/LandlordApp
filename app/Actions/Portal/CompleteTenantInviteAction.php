<?php

namespace App\Actions\Portal;

use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class CompleteTenantInviteAction
{
    public function execute(Tenant $tenant, string $token, string $password): void
    {
        if (! $tenant->matchesInviteToken($token)) {
            abort(403, 'This invite link is invalid or has expired.');
        }

        $tenant->forceFill([
            'password' => Hash::make($password),
            'portal_enabled_at' => $tenant->portal_enabled_at ?? now(),
        ])->save();

        $tenant->clearPortalInvite();
    }
}
