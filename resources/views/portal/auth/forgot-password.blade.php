<x-guest-portal-layout>
    <div class="ui-card-elevated border-gradient p-8">
        <h2 class="text-xl font-semibold text-white">Reset your password</h2>
        <p class="mt-2 text-sm text-slate-400">We will email you a link to choose a new password.</p>

        <x-auth-session-status class="mt-4" :status="session('status')" />

        <form method="POST" action="{{ route('portal.password.email') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center">
                {{ __('Email reset link') }}
            </x-primary-button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-400">
            <a href="{{ route('portal.login') }}" class="font-medium text-brand-300 hover:text-white">Back to sign in</a>
        </p>
    </div>
</x-guest-portal-layout>
