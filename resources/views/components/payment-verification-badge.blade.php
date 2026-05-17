@props(['status'])

@php
    $verificationStatus = $status instanceof \App\Enums\PaymentVerificationStatus
        ? $status
        : \App\Enums\PaymentVerificationStatus::from($status);

    $tone = match ($verificationStatus) {
        \App\Enums\PaymentVerificationStatus::Verified => 'success',
        \App\Enums\PaymentVerificationStatus::Unverified => 'neutral',
        \App\Enums\PaymentVerificationStatus::Disputed => 'danger',
    };
@endphp

<x-ui.badge :tone="$tone" {{ $attributes }}>
    {{ $verificationStatus->label() }}
</x-ui.badge>
