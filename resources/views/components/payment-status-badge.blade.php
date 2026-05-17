@props(['status'])

@php
    $paymentStatus = $status instanceof \App\Enums\PaymentStatus
        ? $status
        : \App\Enums\PaymentStatus::from($status);

    $tone = match ($paymentStatus) {
        \App\Enums\PaymentStatus::Paid => 'success',
        \App\Enums\PaymentStatus::DueSoon => 'warning',
        \App\Enums\PaymentStatus::Overdue => 'danger',
        \App\Enums\PaymentStatus::PartiallyPaid => 'info',
    };
@endphp

<x-ui.badge :tone="$tone" {{ $attributes }}>
    {{ $paymentStatus->label() }}
</x-ui.badge>
