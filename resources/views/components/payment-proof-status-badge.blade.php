@props(['status', 'forTenant' => false])

@php
    $proofStatus = $status instanceof \App\Enums\PaymentProofStatus
        ? $status
        : \App\Enums\PaymentProofStatus::from($status);

    $label = $forTenant ? $proofStatus->tenantLabel() : $proofStatus->label();
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$proofStatus->badgeClasses(),
]) }}>
    {{ $label }}
</span>
