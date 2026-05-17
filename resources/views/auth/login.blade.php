<x-guest-layout>
    <div class="ui-card-elevated border-gradient p-8 sm:p-10">
        <div class="mb-8">
            <h2 class="text-2xl font-semibold tracking-tight text-white">Welcome back</h2>
            <p class="mt-2 text-sm text-slate-400">Sign in to manage rent collection and reminders.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <label for="remember_me" class="inline-flex items-center gap-2">
                    <input id="remember_me" type="checkbox" class="ui-checkbox" name="remember">
                    <span class="text-sm text-slate-400">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium ui-link" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-primary-button class="w-full justify-center">
                {{ __('Sign in') }}
            </x-primary-button>
        </form>

        @if (Route::has('register'))
            <p class="mt-8 text-center text-sm text-slate-400">
                New to {{ config('app.name') }}?
                <a href="{{ route('register') }}" class="font-semibold ui-link">Create an account</a>
            </p>
        @endif
    </div>
</x-guest-layout>
