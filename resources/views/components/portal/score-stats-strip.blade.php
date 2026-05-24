@props(['profile'])

@php
    $stats = $profile->portalCompactStats();
@endphp

<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-3 sm:grid-cols-4']) }}>
    @foreach ($stats as $stat)
        <div class="rounded-xl bg-navy-900/90 p-4 ring-1 ring-white/[0.06]">
            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
            <p class="mt-1.5 text-xl font-bold tabular-nums text-white sm:text-2xl">{{ $stat['value'] }}</p>
        </div>
    @endforeach
</div>
