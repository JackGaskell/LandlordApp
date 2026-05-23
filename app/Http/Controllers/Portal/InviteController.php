<?php

namespace App\Http\Controllers\Portal;

use App\Actions\Portal\CompleteTenantInviteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\CompleteTenantInviteRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InviteController extends Controller
{
    public function show(Request $request, Tenant $tenant): View|RedirectResponse
    {
        $token = (string) $request->query('token', '');

        if (! $tenant->matchesInviteToken($token)) {
            return redirect()
                ->route('portal.login')
                ->with('status', 'This invite link is invalid or has expired. Ask your landlord for a new one.');
        }

        if ($tenant->hasPortalAccess()) {
            return redirect()->route('portal.login')
                ->with('status', 'Your portal is already set up. Sign in with your email and password.');
        }

        return view('portal.auth.invite', [
            'tenant' => $tenant,
            'token' => $token,
        ]);
    }

    public function store(
        CompleteTenantInviteRequest $request,
        Tenant $tenant,
        CompleteTenantInviteAction $completeInvite,
    ): RedirectResponse {
        $token = (string) $request->query('token', '');

        if (! $tenant->matchesInviteToken($token)) {
            abort(403);
        }

        $completeInvite->execute($tenant, $token, $request->validated('password'));

        Auth::guard('tenant')->login($tenant);
        $request->session()->regenerate();
        $tenant->markLoggedIn();

        return redirect()
            ->route('portal.dashboard')
            ->with('status', 'Welcome! Your rent portal is ready.');
    }
}
