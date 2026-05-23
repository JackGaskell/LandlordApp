@props(['profile'])

@php
    $stats = $profile->portalCompactStats();
    $recentTimeline = $profile->portalRecentTimeline();
@endphp

<div class="rounded-2xl bg-white/[0.03] p-5 ring-1 ring-white/[0.06] sm:p-6">
    <div class="grid grid-cols-3 gap-4 sm:gap-6">
        @foreach ($stats as $stat)
            <div class="min-w-0 text-center sm:text-left">
                <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
                <p class="mt-1.5 text-xl font-bold tabular-nums text-white sm:text-2xl">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    @if ($recentTimeline->isNotEmpty())
        <div class="mt-4 flex items-center gap-3 border-t border-white/[0.06] pt-4 sm:mt-5">
            <span class="shrink-0 text-[10px] font-medium uppercase tracking-wider text-slate-600">Recent</span>
            <div class="flex flex-1 items-center justify-center gap-1.5 sm:justify-start">
                @foreach ($recentTimeline->reverse() as $entry)
                    <span
                        class="h-2 w-2 rounded-full {{ $entry->dotClasses() }}"
                        title="{{ $entry->periodLabel }}"
                    ></span>
                @endforeach
            </div>
        </div>
    @endif
</div>
