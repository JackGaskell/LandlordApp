@props([
    'name',
    'label',
    'hint' => null,
    'options' => [],
    'selected' => [],
])

@php
    $selectedValues = collect(old($name, $selected))
        ->map(fn ($day) => (int) $day)
        ->all();
@endphp

<fieldset {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <legend class="ui-label">{{ $label }}</legend>
    @if ($hint)
        <p class="text-sm text-slate-400">{{ $hint }}</p>
    @endif

    <div class="flex flex-wrap gap-2">
        @foreach ($options as $value => $optionLabel)
            @php
                $value = (int) $value;
                $isChecked = in_array($value, $selectedValues, true);
            @endphp
            <label class="ui-chip">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $value }}"
                    class="sr-only"
                    @checked($isChecked)
                >
                <span>{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>

    <x-input-error :messages="$errors->get($name)" />
    <x-input-error :messages="$errors->get($name.'.*')" />
</fieldset>
