@props(['profile'])

@php
    $score = min(100, max(0, (int) round($profile->score)));
    $tier = $profile->scoreTier();
    $radius = 54;
    $circumference = 2 * 3.14159 * $radius;
    $offset = $circumference * (1 - $score / 100);
    $scoreTiers = \App\Enums\TenantScoreTier::ordered();
    $tierIndex = array_search($tier, $scoreTiers, true);
    $rate = min(100, max(0, (int) round($profile->consistencyRate)));
@endphp

<section class="relative overflow-hidden rounded-2xl bg-navy-900/90">
    <div class="pointer-events-none absolute inset-0 bg-brand-gradient-soft opacity-40" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -right-20 -top-20 h-40 w-40 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>

    <div class="relative flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:gap-8 sm:p-6 lg:p-7">
        <div class="relative mx-auto h-36 w-36 shrink-0 sm:mx-0 sm:h-40 sm:w-40">
            <svg class="h-full w-full -rotate-90" viewBox="0 0 128 128" aria-hidden="true">
                <circle cx="64" cy="64" r="{{ $radius }}" fill="none" stroke="currentColor" stroke-width="7" class="text-white/[0.06]" />
                <circle
                    cx="64" cy="64" r="{{ $radius }}" fill="none" stroke="url(#scoreRingGradient)" stroke-width="7"
                    stroke-linecap="round"
                    stroke-dasharray="{{ $circumference }}"
                    stroke-dashoffset="{{ $offset }}"
                    class="transition-all duration-1000 ease-smooth"
                />
                <defs>
                    <linearGradient id="scoreRingGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#3b82f6" />
                        <stop offset="50%" stop-color="#2dd4bf" />
                        <stop offset="100%" stop-color="#84cc16" />
                    </linearGradient>
                </defs>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-5xl font-bold tabular-nums tracking-tight text-white">{{ $profile->scoreFormatted() }}</span>
                <span class="text-[10px] font-medium uppercase tracking-wider text-slate-500">Score</span>
            </div>
        </div>

        <div class="min-w-0 flex-1 text-center sm:text-left">
            <x-portal.score-tier-badge :tier="$tier" class="!px-3 !py-1 !text-xs" />

            <p class="mt-3 text-lg font-semibold leading-snug text-white sm:text-xl">
                {{ $profile->portalHeadline() }}
            </p>

            @if ($hint = $profile->portalProgressHint())
                <p class="mt-1 text-sm font-medium text-brand-300">{{ $hint }}</p>
            @endif

            <p class="mt-2 line-clamp-2 text-sm text-slate-400">{{ $profile->portalCompactMessage() }}</p>

            <div class="mt-4">
                <div class="flex gap-0.5">
                    @foreach ($scoreTiers as $index => $scaleTier)
                        <div @class([
                            'h-1 flex-1 rounded-full',
                            'bg-brand-gradient' => $index <= $tierIndex,
                            'bg-white/[0.08]' => $index > $tierIndex,
                        ])></div>
                    @endforeach
                </div>
                <div class="mt-1.5 flex justify-between text-[10px] font-medium text-slate-600">
                    <span>{{ $scoreTiers[0]->scaleLabel() }}</span>
                    <span>{{ $scoreTiers[count($scoreTiers) - 1]->scaleLabel() }}</span>
                </div>
            </div>

            @if ($profile->trackedPeriods > 0)
                <div class="mt-3 flex items-center gap-2">
                    <div class="h-1 flex-1 overflow-hidden rounded-full bg-white/[0.06]">
                        <div class="h-full rounded-full bg-brand-gradient" style="width: {{ $rate }}%"></div>
                    </div>
                    <span class="shrink-0 text-xs tabular-nums text-slate-500">{{ $profile->consistencyFormatted() }}% on time</span>
                </div>
            @endif
        </div>
    </div>
</section>
