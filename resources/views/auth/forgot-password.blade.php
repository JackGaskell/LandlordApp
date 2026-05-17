<x-guest-layout>
    <div class="ui-card-elevated border-gradient p-8 sm:p-10">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-white">Reset password</h2>
            <p class="mt-2 text-sm text-slate-400">
                Enter your email and we will send you a link to choose a new password.
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <x-primary-button class="w-full justify-center">{{ __('Email reset link') }}</x-primary-button>
        </form>
    </div>
</x-guest-layout>
