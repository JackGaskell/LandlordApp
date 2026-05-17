@props(['status'])

@php
    $tenantStatus = $status instanceof \App\Enums\TenantStatus
        ? $status
        : \App\Enums\TenantStatus::from($status);

    $tone = match ($tenantStatus) {
        \App\Enums\TenantStatus::Active => 'success',
        \App\Enums\TenantStatus::Inactive => 'neutral',
        \App\Enums\TenantStatus::Archived => 'neutral',
    };
@endphp

<x-ui.badge :tone="$tone" {{ $attributes }}>
    {{ $tenantStatus->label() }}
</x-ui.badge>
