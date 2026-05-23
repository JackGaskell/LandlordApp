@props(['profile'])

@php
    $score = min(100, max(0, (int) round($profile->score)));
    $radius = 54;
    $circumference = 2 * 3.14159 * $radius;
    $offset = $circumference * (1 - $score / 100);
@endphp

<section class="relative overflow-hidden rounded-2xl bg-navy-900/90">
    <div class="pointer-events-none absolute inset-0 bg-brand-gradient-soft opacity-40" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -right-20 -top-20 h-40 w-40 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>

    <div class="relative p-5 sm:p-6 lg:p-7">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:gap-8">
            <div class="relative mx-auto h-32 w-32 shrink-0 sm:mx-0 sm:h-36 sm:w-36">
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
                    <span class="text-4xl font-bold tabular-nums tracking-tight text-white sm:text-5xl">{{ $profile->scoreFormatted() }}</span>
                    <span class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-slate-500">Tenant score</span>
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <x-portal.score-progression :profile="$profile" />
            </div>
        </div>
    </div>
</section>
