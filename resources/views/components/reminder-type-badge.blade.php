@props(['type'])

@php
    $reminderType = $type instanceof \App\Enums\ReminderType
        ? $type
        : \App\Enums\ReminderType::from($type);
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ring-black/5 '.$reminderType->badgeClasses(),
]) }}>
    {{ $reminderType->label() }}
</span>
