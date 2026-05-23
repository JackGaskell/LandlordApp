<?php

namespace App\Actions\Portal;

use App\Models\Tenant;
use Illuminate\Support\Facades\URL;

class EnableTenantPortalAction
{
    /**
     * Enable portal access and return a signed invite URL for the tenant to set their password.
     */
    public function execute(Tenant $tenant): string
    {
        $plainToken = $tenant->issuePortalInvite();

        return URL::temporarySignedRoute(
            'portal.invite.show',
            $tenant->portal_invite_expires_at ?? now()->addDays(7),
            [
                'tenant' => $tenant->id,
                'token' => $plainToken,
            ],
        );
    }
}
