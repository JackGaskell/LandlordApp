@props(['profile'])

@php
    $stats = $profile->portalCompactStats();
    $recentTimeline = $profile->portalRecentTimeline();
@endphp

<div class="grid grid-cols-3 gap-3 rounded-2xl bg-white/[0.03] p-3 sm:gap-4 sm:p-4">
    @foreach ($stats as $stat)
        <div class="min-w-0">
            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
            <p class="mt-1 text-xl font-bold tabular-nums text-white sm:text-2xl">{{ $stat['value'] }}</p>
        </div>
    @endforeach
</div>

@if ($recentTimeline->isNotEmpty())
    <div class="mt-2 flex items-center gap-2 px-1">
        <span class="text-[10px] font-medium uppercase tracking-wider text-slate-600">Recent</span>
        <div class="flex flex-1 items-center gap-1.5">
            @foreach ($recentTimeline->reverse() as $entry)
                <span
                    class="h-2 w-2 rounded-full {{ $entry->dotClasses() }}"
                    title="{{ $entry->periodLabel }}"
                ></span>
            @endforeach
        </div>
    </div>
@endif
