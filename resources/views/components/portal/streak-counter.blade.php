@props(['profile'])

<div class="ui-card-elevated flex h-full flex-col overflow-hidden p-6">
    <div class="flex items-start justify-between gap-3">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">On-time streak</p>
        @if ($profile->currentStreak > 0)
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/15 text-orange-400" title="Keep it going">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 23c-3 0-5.5-1.5-7.5-4.5C2.5 15 2 12 2 9c0-4 3-7 7-7 2 0 3.5.5 5 2C15.5 2 17 1.5 19 1.5c4 0 7 3 7 7 0 3-.5 6-2.5 9.5C21.5 21.5 19 23 16 23h-4Z"/>
                </svg>
            </span>
        @endif
    </div>

    <div class="mt-4 flex items-baseline gap-2">
        <p class="text-6xl font-bold tabular-nums tracking-tight text-white">{{ $profile->currentStreak }}</p>
        <p class="text-sm text-slate-400">{{ str('month')->plural($profile->currentStreak) }}</p>
    </div>

    <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-400">
        @if ($profile->currentStreak >= 3)
            You are on a roll — consistency like this builds real trust with your landlord.
        @elseif ($profile->currentStreak >= 1)
            One on-time payment at a time. Your next due date is a chance to extend this streak.
        @else
            Your next on-time payment starts a fresh streak. Small wins add up.
        @endif
    </p>

    @if ($profile->bestStreak > $profile->currentStreak)
        <p class="mt-4 border-t border-white/[0.06] pt-4 text-xs text-slate-500">
            Personal best: <span class="font-medium text-slate-300">{{ $profile->bestStreak }}</span> {{ str('month')->plural($profile->bestStreak) }}
        </p>
    @endif
</div>
