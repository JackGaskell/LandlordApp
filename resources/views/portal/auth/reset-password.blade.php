<x-guest-portal-layout>
    <div class="ui-card-elevated border-gradient p-8">
        <h2 class="text-xl font-semibold text-white">Choose a new password</h2>
        <p class="mt-2 text-sm text-slate-400">Pick something secure — you will use it to sign in next time.</p>

        <form method="POST" action="{{ route('portal.password.store') }}" class="mt-6 space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="password_confirmation" class="mt-1.5 block w-full" type="password" name="password_confirmation" required />
            </div>

            <x-primary-button class="w-full justify-center">
                Save password
            </x-primary-button>
        </form>
    </div>
</x-guest-portal-layout>
