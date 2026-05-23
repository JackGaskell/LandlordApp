@props(['profile'])

@php
    $rate = min(100, max(0, (int) round($profile->consistencyRate)));
@endphp

<div class="ui-card-elevated flex h-full flex-col p-6">
    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Collection consistency</p>
    <p class="mt-1 text-xs text-slate-500">Last {{ $profile->consistencyWindowMonths }} months</p>

    <div class="mt-6 flex items-end gap-3">
        <p class="text-4xl font-bold tabular-nums text-white">{{ $profile->consistencyFormatted() }}<span class="text-xl text-slate-500">%</span></p>
        <p class="mb-1 text-sm text-slate-400">on-time</p>
    </div>

    <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/[0.06]">
        <div
            class="h-full rounded-full bg-brand-gradient transition-all duration-700 ease-smooth"
            style="width: {{ $rate }}%"
        ></div>
    </div>

    <p class="mt-4 flex-1 text-sm text-slate-400">
        @if ($rate >= 90)
            Strong consistency — landlords notice tenants who pay like clockwork.
        @elseif ($rate >= 70)
            You are mostly on track. A few more on-time months will push this higher.
        @else
            Each on-time month improves this rate. Focus on the next due date.
        @endif
    </p>
</div>
