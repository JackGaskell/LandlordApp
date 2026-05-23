@props(['profile'])

<section class="rounded-2xl border border-white/[0.08] bg-navy-900/80 p-6 shadow-card-dark sm:p-8">
    <div class="flex items-center gap-5">
        <div @class([
            'flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl',
            'bg-orange-500/15 text-orange-400' => $profile->currentStreak > 0,
            'bg-white/[0.04] text-slate-500' => $profile->currentStreak === 0,
        ])>
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 23c-3 0-5.5-1.5-7.5-4.5C2.5 15 2 12 2 9c0-4 3-7 7-7 2 0 3.5.5 5 2C15.5 2 17 1.5 19 1.5c4 0 7 3 7 7 0 3-.5 6-2.5 9.5C21.5 21.5 19 23 16 23h-4Z"/>
            </svg>
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-slate-400">Payment streak</p>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-3xl font-bold tabular-nums tracking-tight text-white">{{ $profile->currentStreak }}</span>
                <span class="text-sm text-slate-500">{{ str('month')->plural($profile->currentStreak) }} on time</span>
            </div>
        </div>

        @if ($profile->bestStreak > $profile->currentStreak)
            <div class="hidden shrink-0 text-right sm:block">
                <p class="text-xs text-slate-500">Personal best</p>
                <p class="text-sm font-semibold text-slate-300">{{ $profile->bestStreak }}</p>
            </div>
        @endif
    </div>

    <p class="mt-4 text-sm leading-relaxed text-slate-400">
        @if ($profile->currentStreak >= 3)
            Keep your streak alive — every on-time month builds a trusted tenant profile.
        @elseif ($profile->currentStreak >= 1)
            You're building momentum. Your next on-time payment extends this streak.
        @else
            Your next on-time payment starts a fresh streak. Small wins add up.
        @endif
    </p>
</section>
