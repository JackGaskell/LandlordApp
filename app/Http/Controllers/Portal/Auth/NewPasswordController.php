<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('portal.auth.reset-password', [
            'request' => $request,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::broker('tenants')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Tenant $tenant, string $password) {
                $tenant->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'portal_enabled_at' => $tenant->portal_enabled_at ?? now(),
                ])->save();

                event(new PasswordReset($tenant));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('portal.login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
