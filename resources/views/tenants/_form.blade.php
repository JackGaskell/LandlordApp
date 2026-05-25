@php
    use App\Enums\TenantStatus;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="mt-1.5 block w-full" :value="old('name', $tenant->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="property_label" value="Property label (optional)" />
        <x-text-input id="property_label" name="property_label" class="mt-1.5 block w-full" :value="old('property_label', $tenant->property_label ?? '')" placeholder="e.g. 12 High Street" />
        <x-input-error :messages="$errors->get('property_label')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email', $tenant->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone_number" value="Phone" />
        <x-text-input id="phone_number" name="phone_number" class="mt-1.5 block w-full" :value="old('phone_number', $tenant->phone_number ?? '')" />
        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rent_amount" value="Monthly rent (£)" />
        <x-text-input id="rent_amount" name="rent_amount" type="number" step="0.01" class="mt-1.5 block w-full" :value="old('rent_amount', $tenant->rent_amount ?? '')" required />
        <x-input-error :messages="$errors->get('rent_amount')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="rent_due_day" value="Rent due day (1–28)" />
        <x-text-input id="rent_due_day" name="rent_due_day" type="number" min="1" max="28" class="mt-1.5 block w-full" :value="old('rent_due_day', $tenant->rent_due_day ?? 1)" required />
        <x-input-error :messages="$errors->get('rent_due_day')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="ui-select mt-1.5 block w-full">
            @foreach (TenantStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $tenant->status->value ?? TenantStatus::Active->value) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="ui-input mt-1.5">{{ old('notes', $tenant->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>
