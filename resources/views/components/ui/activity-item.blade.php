@props(['payment'])

@php
    /** @var \App\Models\PaymentHistory $payment */
    $tenant = $payment->tenant;
@endphp

<div class="flex items-center gap-4 px-4 py-3.5 transition-colors hover:bg-white/[0.03]">
    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-gradient text-sm font-bold text-white shadow-sm">
        {{ strtoupper(substr($tenant->name, 0, 1)) }}
    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-medium text-white">
            <a href="{{ route('tenants.show', $tenant) }}" class="ui-link">{{ $tenant->name }}</a>
        </p>
        <p class="text-xs text-slate-400">
            Due {{ $payment->due_date->format('j M Y') }}
            · £{{ number_format($payment->amount, 2) }}
        </p>
    </div>
    <div class="shrink-0 text-right">
        <x-payment-status-badge :status="$payment->status" />
        <p class="mt-1.5 text-[11px] text-slate-400">{{ $payment->updated_at->diffForHumans() }}</p>
    </div>
</div>
