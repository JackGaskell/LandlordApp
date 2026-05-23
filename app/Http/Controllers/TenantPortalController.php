<?php

namespace App\Http\Controllers;

use App\Actions\Portal\EnableTenantPortalAction;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantPortalController extends Controller
{
    public function store(Request $request, Tenant $tenant, EnableTenantPortalAction $enablePortal): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $inviteUrl = $enablePortal->execute($tenant);

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('status', 'Tenant portal invite link created.')
            ->with('portal_invite_url', $inviteUrl);
    }
}
