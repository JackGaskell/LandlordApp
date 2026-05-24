<x-guest-portal-layout>
    <div class="ui-card-elevated border-gradient p-8">
        <h2 class="text-xl font-semibold text-white">Create your account</h2>
        <p class="mt-2 text-sm text-slate-400">
            Hi {{ $tenant->name }} — choose a password and you are ready to track rent and your score anytime.
        </p>

        <form method="POST" action="{{ request()->fullUrl() }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required autofocus />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="password_confirmation" class="mt-1.5 block w-full" type="password" name="password_confirmation" required />
            </div>

            <x-primary-button class="w-full justify-center">
                Get started
            </x-primary-button>
        </form>
    </div>
</x-guest-portal-layout>
