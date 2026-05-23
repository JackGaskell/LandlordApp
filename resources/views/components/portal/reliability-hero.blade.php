@props(['profile'])

@php
    $score = min(100, max(0, (int) round($profile->score)));
    $circumference = 2 * 3.14159 * 54;
    $offset = $circumference * (1 - $score / 100);
@endphp

<div class="ui-card-elevated relative overflow-hidden p-6 sm:p-8">
    <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-brand-500/10 blur-3xl"></div>

    <div class="relative flex flex-col items-center text-center sm:flex-row sm:items-center sm:gap-8 sm:text-left">
        <div class="relative h-32 w-32 shrink-0">
            <svg class="h-32 w-32 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                <circle cx="60" cy="60" r="54" fill="none" stroke="currentColor" stroke-width="8" class="text-white/[0.06]" />
                <circle
                    cx="60" cy="60" r="54" fill="none" stroke="url(#reliabilityGradient)" stroke-width="8"
                    stroke-linecap="round"
                    stroke-dasharray="{{ $circumference }}"
                    stroke-dashoffset="{{ $offset }}"
                    class="transition-all duration-700 ease-smooth"
                />
                <defs>
                    <linearGradient id="reliabilityGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#3b82f6" />
                        <stop offset="50%" stop-color="#2dd4bf" />
                        <stop offset="100%" stop-color="#84cc16" />
                    </linearGradient>
                </defs>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-3xl font-bold tracking-tight text-white">{{ $profile->scoreFormatted() }}</span>
                <span class="text-[10px] font-medium uppercase tracking-wider text-slate-500">Score</span>
            </div>
        </div>

        <div class="mt-6 min-w-0 flex-1 sm:mt-0">
            <x-portal.reliability-badge :profile="$profile" class="mb-3" />
            <h2 class="text-lg font-semibold text-white">{{ $profile->portalHeadline() }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $profile->portalMessage() }}</p>

            <dl class="mt-5 grid grid-cols-3 gap-3 text-center sm:text-left">
                <div>
                    <dt class="text-[10px] font-medium uppercase tracking-wider text-slate-500">On time</dt>
                    <dd class="mt-1 text-lg font-semibold text-emerald-400">{{ $profile->totalOnTime }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-medium uppercase tracking-wider text-slate-500">Late</dt>
                    <dd class="mt-1 text-lg font-semibold text-amber-400">{{ $profile->lateCount }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-medium uppercase tracking-wider text-slate-500">Tracked</dt>
                    <dd class="mt-1 text-lg font-semibold text-white">{{ $profile->trackedPeriods }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
