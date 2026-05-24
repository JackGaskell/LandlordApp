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

<div class="w-full min-w-0">
    <div class="space-y-1 text-center">
        <p class="text-sm font-semibold text-white">
            @if ($scoreEstablished && $tier)
                {{ "You're at the" }} <span class="text-gradient font-semibold">{{ $tier->scaleLabel() }}</span> stage
            @else
                {{ $profile->portalProgressionCurrentLine() }}
            @endif
        </p>
        <p class="text-sm font-medium text-brand-300">
            {{ $profile->portalProgressionNextLine() }}
        </p>
        <p class="text-xs text-slate-500">
            {{ $profile->portalProgressionSupportLine() }}
        </p>
        @if ($milestoneNudge = $profile->portalMilestoneNudgeMessage())
            <p class="text-xs text-brand-300/80">{{ $milestoneNudge }}</p>
        @endif
        @if ($scoreImpact = $profile->portalScoreImpactMessage())
            <p class="mt-2 text-xs leading-relaxed text-slate-400">{{ $scoreImpact }}</p>
        @endif
    </div>

    <div class="relative mt-5 w-full">
        <div
            class="absolute left-[10%] right-[10%] top-3 h-0.5 rounded-full bg-white/[0.08]"
            aria-hidden="true"
        >
            <div
                class="h-full rounded-full bg-brand-gradient transition-all duration-700"
                style="width: {{ $trackFill }}%"
            ></div>
        </div>

        <ol class="relative flex w-full justify-between">
            @foreach ($stages as $index => $stage)
                @php
                    $isCurrent = $scoreEstablished && $stage === $tier;
                    $isComplete = $scoreEstablished && $currentIndex !== false && $index < $currentIndex;
                    $isFuture = ! $isCurrent && ! $isComplete;
                @endphp
                <li class="flex min-w-0 flex-1 flex-col items-center">
                    @if ($isCurrent)
                        <span
                            class="relative z-10 flex h-6 w-6 shrink-0 items-center justify-center"
                            aria-current="step"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="11" fill="url(#scoreArcGradient)" />
                            </svg>
                        </span>
                    @else
                        <span
                            @class([
                                'relative z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full ring-2',
                                'bg-emerald-500/10 ring-emerald-500/30' => $isComplete,
                                'bg-navy-900 ring-white/10' => $isFuture,
                            ])
                        >
                            @if ($isComplete)
                                <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5" />
                                </svg>
                            @else
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-600"></span>
                            @endif
                        </span>
                    @endif
                    <span @class([
                        'mt-2 w-full truncate text-center text-[10px] font-medium leading-tight sm:text-xs',
                        'font-semibold text-brand-300' => $isCurrent,
                        'text-slate-400' => $isComplete,
                        'text-slate-600' => $isFuture,
                    ])>{{ $stage->scaleLabel() }}</span>
                    <span @class([
                        'mt-0.5 text-center text-[9px] tabular-nums leading-tight',
                        'font-medium text-brand-400/70' => $isCurrent,
                        'text-slate-500' => $isComplete,
                        'text-slate-600' => $isFuture,
                    ])>{{ $stage->scoreRangeLabel() }}</span>
                </li>
            @endforeach
        </ol>
    </div>
</div>
