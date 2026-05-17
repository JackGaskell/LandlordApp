<x-guest-layout>
    <div class="ui-card-elevated border-gradient p-8 sm:p-10">
        <div class="mb-8">
            <h2 class="text-2xl font-semibold tracking-tight text-white">Create your account</h2>
            <p class="mt-2 text-sm text-slate-400">Start tracking rent and automating reminders in minutes.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="first_name" :value="__('First name')" />
                    <x-text-input id="first_name" class="mt-1.5 block w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="last_name" :value="__('Last name')" />
                    <x-text-input id="last_name" class="mt-1.5 block w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="password_confirmation" class="mt-1.5 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center">{{ __('Create account') }}</x-primary-button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-400">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold ui-link">Sign in</a>
        </p>
    </div>
</x-guest-layout>
