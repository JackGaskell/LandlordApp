<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->user('tenant');

        if (! $tenant || ! $tenant->hasPortalAccess()) {
            auth()->guard('tenant')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('portal.login')
                ->with('status', 'Your account is not active yet. Use the invite link you were sent to get started.');
        }

        return $next($request);
    }
}
