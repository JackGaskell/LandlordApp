@props(['profile'])

@php
    $scoreEstablished = $profile->portalScoreIsEstablished();
    $tier = $scoreEstablished ? $profile->scoreTier() : null;
    $stages = \App\Enums\TenantScoreTier::ordered();
    $currentIndex = $tier !== null ? array_search($tier, $stages, true) : false;
    $trackFill = $scoreEstablished && count($stages) > 1 && $currentIndex !== false
        ? min(100, max(0, ($currentIndex / (count($stages) - 1)) * 100))
        : 0;
@endphp

<div class="min-w-0 sm:pt-2">
    <div class="space-y-1">
        <p class="text-sm font-semibold text-white">
            {{ $profile->portalProgressionCurrentLine() }}
        </p>
        <p class="text-sm font-medium text-brand-300">
            {{ $profile->portalProgressionNextLine() }}
        </p>
        <p class="text-xs text-slate-500">
            {{ $profile->portalProgressionSupportLine() }}
        </p>
    </div>

    <div class="relative mt-5">
        <div class="absolute left-0 right-0 top-[11px] h-0.5 rounded-full bg-white/[0.08]" aria-hidden="true">
            <div class="h-full rounded-full bg-brand-gradient transition-all duration-700" style="width: {{ $trackFill }}%"></div>
        </div>

        <ol class="relative flex justify-between gap-1">
            @foreach ($stages as $index => $stage)
                @php
                    $isCurrent = $scoreEstablished && $stage === $tier;
                    $isComplete = $scoreEstablished && $currentIndex !== false && $index < $currentIndex;
                    $isFuture = ! $isCurrent && ! $isComplete;
                @endphp
                <li class="flex min-w-0 flex-1 flex-col items-center">
                    <span
                        @class([
                            'relative z-10 flex h-6 w-6 items-center justify-center rounded-full ring-2 transition-all',
                            'bg-brand-gradient ring-brand-400/40 shadow-glow' => $isCurrent,
                            'bg-accent-teal/20 ring-accent-teal/30' => $isComplete,
                            'bg-navy-900 ring-white/10' => $isFuture,
                        ])
                        @if ($isCurrent) aria-current="step" @endif
                    >
                        @if ($isComplete)
                            <svg class="h-3 w-3 text-accent-teal" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5" />
                            </svg>
                        @elseif ($isCurrent)
                            <span class="h-2 w-2 rounded-full bg-white"></span>
                        @else
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-600"></span>
                        @endif
                    </span>
                    <span @class([
                        'mt-2 w-full truncate text-center text-[10px] font-medium leading-tight sm:text-xs',
                        'text-white' => $isCurrent,
                        'text-slate-400' => $isComplete,
                        'text-slate-600' => $isFuture,
                    ])>{{ $stage->scaleLabel() }}</span>
                    <span @class([
                        'mt-0.5 text-center text-[9px] tabular-nums leading-tight',
                        'text-slate-500' => $isCurrent || $isComplete,
                        'text-slate-700' => $isFuture,
                    ])>{{ $stage->scoreRangeLabel() }}</span>
                </li>
            @endforeach
        </ol>
    </div>
</div>
