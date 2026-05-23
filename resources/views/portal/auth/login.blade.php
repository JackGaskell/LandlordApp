<x-guest-portal-layout>
    <div class="ui-card-elevated border-gradient p-8">
        <h2 class="text-xl font-semibold text-white">Welcome back</h2>
        <p class="mt-2 text-sm text-slate-400">Sign in to view your rent, streak, and payment history.</p>

        @if (app()->environment('local'))
            <div class="mt-4 rounded-xl border border-brand-500/20 bg-brand-500/10 px-4 py-3 text-sm text-slate-300">
                <p class="font-medium text-white">Demo tenant accounts</p>
                <p class="mt-1 text-xs text-slate-400">Password for all: <span class="font-mono text-slate-300">password</span></p>
                <ul class="mt-2 space-y-1 text-xs">
                    <li><span class="font-mono text-brand-200">jamie.tenant@landlordapp.test</span> — upcoming rent</li>
                    <li><span class="font-mono text-brand-200">casey.overdue@landlordapp.test</span> — overdue rent</li>
                    <li><span class="font-mono text-brand-200">riley.ontrack@landlordapp.test</span> — on track</li>
                </ul>
                <p class="mt-2 text-xs text-slate-500">Landlords use <span class="font-mono">/login</span> with <span class="font-mono">alex@landlordapp.test</span>.</p>
            </div>
        @endif

        <x-auth-session-status class="mt-4" :status="session('status')" />

        <form method="POST" action="{{ route('portal.login') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <label for="remember_me" class="inline-flex items-center gap-2">
                    <input id="remember_me" type="checkbox" class="ui-checkbox" name="remember">
                    <span class="text-sm text-slate-400">{{ __('Remember me') }}</span>
                </label>

                <a class="text-sm font-medium text-brand-300 hover:text-white" href="{{ route('portal.password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            </div>

            <x-primary-button class="w-full justify-center">
                {{ __('Sign in') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-portal-layout>
