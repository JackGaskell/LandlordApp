<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Tenant name" />
        <x-text-input id="name" name="name" class="mt-1.5 block w-full" :value="old('name')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email')" required />
        <p class="mt-1.5 text-xs text-slate-500">Used for rent reminders and their portal invite.</p>
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="property_label" value="Property label (optional)" />
        <x-text-input id="property_label" name="property_label" class="mt-1.5 block w-full" :value="old('property_label')" placeholder="e.g. 12 High Street" />
        <p class="mt-1.5 text-xs text-slate-500">Helps you recognise this rent if you have several at the same address.</p>
        <x-input-error :messages="$errors->get('property_label')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rent_amount" value="Monthly rent (£)" />
        <x-text-input id="rent_amount" name="rent_amount" type="number" step="0.01" class="mt-1.5 block w-full" :value="old('rent_amount')" required />
        <x-input-error :messages="$errors->get('rent_amount')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rent_due_day" value="Rent due day (1–28)" />
        <x-text-input id="rent_due_day" name="rent_due_day" type="number" min="1" max="28" class="mt-1.5 block w-full" :value="old('rent_due_day', 1)" required />
        <p class="mt-1.5 text-xs text-slate-500">We open each rent period and send reminders automatically.</p>
        <x-input-error :messages="$errors->get('rent_due_day')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="phone_number" value="Phone (optional)" />
        <x-text-input id="phone_number" name="phone_number" class="mt-1.5 block w-full" :value="old('phone_number')" />
        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
    </div>
</div>
