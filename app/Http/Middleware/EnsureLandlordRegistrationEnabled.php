<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLandlordRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('landlord.auth.registration_enabled')) {
            abort(404);
        }

        return $next($request);
    }
}
