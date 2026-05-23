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

<div class="min-w-0">
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
        <div class="absolute inset-x-0 top-[14px] h-0.5 rounded-full bg-white/[0.08]" aria-hidden="true">
            <div
                class="h-full rounded-full bg-brand-gradient transition-all duration-700"
                style="width: {{ $trackFill }}%"
            ></div>
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
                        class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full ring-4 ring-navy-900"
                        @if ($isCurrent) aria-current="step" @endif
                    >
                        @if ($isCurrent)
                            <svg class="h-5 w-5" viewBox="0 0 20 20" aria-hidden="true">
                                <circle cx="10" cy="10" r="10" fill="url(#scoreArcGradient)" />
                            </svg>
                        @elseif ($isComplete)
                            <svg class="h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5" />
                            </svg>
                        @else
                            <span class="h-4 w-4 rounded-full border border-white/20 bg-navy-900"></span>
                        @endif
                    </span>
                    <span @class([
                        'mt-1.5 w-full truncate text-center leading-tight',
                        'text-xs font-semibold text-brand-300 sm:text-sm' => $isCurrent,
                        'text-[10px] font-medium text-slate-500' => $isComplete,
                        'text-[10px] text-slate-600' => $isFuture,
                    ])>{{ $stage->scaleLabel() }}</span>
                    <span @class([
                        'mt-0.5 text-center text-[9px] tabular-nums leading-tight',
                        'font-medium text-brand-400/70' => $isCurrent,
                        'text-slate-600' => $isComplete || $isFuture,
                    ])>{{ $stage->scoreRangeLabel() }}</span>
                </li>
            @endforeach
        </ol>
    </div>
</div>
