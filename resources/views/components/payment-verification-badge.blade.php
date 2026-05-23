@props(['status'])

@php
    $verification = $status instanceof \App\Enums\PaymentVerificationStatus
        ? $status
        : \App\Enums\PaymentVerificationStatus::from($status);
@endphp

<x-ui.badge :tone="$verification->badgeTone()" {{ $attributes }}>
    {{ $verification->label() }}
</x-ui.badge>
