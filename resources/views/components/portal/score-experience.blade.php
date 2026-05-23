@props(['profile'])

@php
    $score = min(100, max(0, (int) round($profile->score)));
    $tier = $profile->scoreTier();
    $nextTier = $profile->portalNextTier();
    $pointsToNext = $profile->portalPointsToNextTier();
    $radius = 78;
    $circumference = 2 * 3.14159 * $radius;
    $offset = $circumference * (1 - $score / 100);
    $achievements = $profile->portalAchievements();
    $stats = $profile->portalScoreStats();
    $recentTimeline = $profile->portalRecentTimeline();
    $scoreTiers = \App\Enums\TenantScoreTier::ordered();
    $tierIndex = array_search($tier, $scoreTiers, true);
    $excellentTier = \App\Enums\TenantScoreTier::Excellent;
@endphp

<section class="relative overflow-hidden rounded-3xl border border-white/[0.08] bg-navy-900 shadow-card-dark">
    <div class="pointer-events-none absolute inset-0 bg-brand-gradient-soft opacity-50" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -left-32 -top-32 h-72 w-72 rounded-full bg-brand-500/10 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-20 -right-20 h-56 w-56 rounded-full bg-accent-teal/10 blur-3xl" aria-hidden="true"></div>

    <div class="relative">
        {{-- Score hero --}}
        <div class="px-6 pb-2 pt-10 text-center sm:px-10 sm:pt-12">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Tenant score</p>
            <p class="mx-auto mt-4 max-w-sm text-sm leading-relaxed text-slate-400">{{ $profile->portalWhyItMatters() }}</p>
        </div>

        <div class="flex flex-col items-center px-6 sm:px-10">
            <div class="relative h-52 w-52 sm:h-56 sm:w-56">
                <svg class="h-full w-full -rotate-90" viewBox="0 0 176 176" aria-hidden="true">
                    <circle cx="88" cy="88" r="{{ $radius }}" fill="none" stroke="currentColor" stroke-width="8" class="text-white/[0.05]" />
                    <circle
                        cx="88" cy="88" r="{{ $radius }}" fill="none" stroke="url(#tenantScoreGradient)" stroke-width="8"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                        class="transition-all duration-1000 ease-smooth"
                    />
                    <defs>
                        <linearGradient id="tenantScoreGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#3b82f6" />
                            <stop offset="50%" stop-color="#2dd4bf" />
                            <stop offset="100%" stop-color="#84cc16" />
                        </linearGradient>
                    </defs>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-6xl font-bold tabular-nums tracking-tight text-white sm:text-7xl">{{ $profile->scoreFormatted() }}</span>
                    <span class="mt-1 text-xs font-medium text-slate-500">out of 100</span>
                </div>
            </div>

            <div class="mt-8">
                <x-portal.score-tier-badge :tier="$tier" />
            </div>

            <p class="mt-5 max-w-md text-center text-base font-medium leading-snug text-white sm:text-lg">
                {{ $profile->portalHeadline() }}
            </p>
            <p class="mt-2 max-w-sm text-center text-sm leading-relaxed text-slate-400">
                {{ $profile->portalMessage() }}
            </p>
        </div>

        {{-- Tier scale --}}
        <div class="mx-auto mt-10 max-w-lg px-6 sm:px-10">
            <div class="flex gap-1">
                @foreach ($scoreTiers as $index => $scaleTier)
                    @php
                        $isActive = $scaleTier === $tier;
                        $isPassed = $index < $tierIndex;
                    @endphp
                    <div class="flex-1">
                        <div @class([
                            'h-1 rounded-full transition-colors',
                            'bg-brand-gradient' => $isActive || $isPassed,
                            'bg-white/[0.08]' => ! $isActive && ! $isPassed,
                        ])></div>
                        <p @class([
                            'mt-2 hidden text-center text-[10px] font-medium leading-tight sm:block',
                            'text-white' => $isActive,
                            'text-slate-500' => ! $isActive,
                        ])>{{ $scaleTier->label() }}</p>
                    </div>
                @endforeach
            </div>
            @if ($nextTier && $pointsToNext !== null && $profile->trackedPeriods > 0)
                <p class="mt-4 text-center text-xs text-slate-500">
                    @if ($pointsToNext > 0)
                        <span class="text-slate-300">{{ $pointsToNext }} points</span> to reach {{ $nextTier->label() }}
                    @else
                        Your next on-time payment could reach {{ $nextTier->label() }}
                    @endif
                </p>
            @elseif ($profile->trackedPeriods > 0 && $tier === $excellentTier)
                <p class="mt-4 text-center text-xs text-slate-500">You have reached the highest tier</p>
            @endif
        </div>

        {{-- Streak --}}
        <div class="mx-6 mt-10 rounded-2xl border border-white/[0.06] bg-white/[0.02] p-5 sm:mx-10">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div @class([
                        'flex h-12 w-12 items-center justify-center rounded-xl',
                        'bg-orange-500/15 text-orange-400' => $profile->currentStreak > 0,
                        'bg-white/[0.04] text-slate-500' => $profile->currentStreak === 0,
                    ])>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 23c-3 0-5.5-1.5-7.5-4.5C2.5 15 2 12 2 9c0-4 3-7 7-7 2 0 3.5.5 5 2C15.5 2 17 1.5 19 1.5c4 0 7 3 7 7 0 3-.5 6-2.5 9.5C21.5 21.5 19 23 16 23h-4Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Payment streak</p>
                        <p class="mt-0.5 text-2xl font-bold tabular-nums text-white">
                            {{ $profile->currentStreak }}
                            <span class="text-sm font-medium text-slate-500">{{ str('month')->plural($profile->currentStreak) }}</span>
                        </p>
                    </div>
                </div>
                @if ($profile->bestStreak > $profile->currentStreak)
                    <div class="text-right">
                        <p class="text-xs text-slate-500">Best</p>
                        <p class="text-sm font-semibold text-slate-300">{{ $profile->bestStreak }}</p>
                    </div>
                @endif
            </div>

            @if ($recentTimeline->isNotEmpty())
                <div class="mt-4 flex items-center justify-center gap-2">
                    @foreach ($recentTimeline->reverse() as $entry)
                        <span
                            class="h-2.5 w-2.5 rounded-full ring-2 ring-navy-900 {{ $entry->dotClasses() }}"
                            title="{{ $entry->periodLabel }} · {{ $entry->outcome->label() }}"
                        ></span>
                    @endforeach
                </div>
            @endif

            <p class="mt-4 text-center text-sm text-slate-400">{{ $profile->portalStreakMessage() }}</p>
        </div>

        {{-- Stats --}}
        <dl class="mt-8 grid grid-cols-3 divide-x divide-white/[0.06] border-y border-white/[0.06]">
            @foreach ($stats as $stat)
                <div class="px-4 py-5 text-center sm:px-6">
                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</dt>
                    <dd class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stat['value'] }}</dd>
                    <dd class="mt-1 text-[11px] text-slate-500">{{ $stat['hint'] }}</dd>
                </div>
            @endforeach
        </dl>

        {{-- Achievements --}}
        <div class="px-6 py-6 sm:px-10">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Milestones</p>
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach ($achievements as $achievement)
                    <li @class([
                        'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium ring-1',
                        'bg-white/[0.06] text-slate-300 ring-white/10' => $achievement['unlocked'],
                        'bg-white/[0.02] text-slate-600 ring-white/[0.04]' => ! $achievement['unlocked'],
                    ])>
                        @if ($achievement['unlocked'])
                            <svg class="h-3.5 w-3.5 text-accent-teal" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <span class="h-3.5 w-3.5 rounded-full border border-white/10" aria-hidden="true"></span>
                        @endif
                        {{ $achievement['label'] }}
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Why + How --}}
        <div class="grid gap-px border-t border-white/[0.06] bg-white/[0.06] sm:grid-cols-2">
            <div class="bg-navy-900 px-6 py-6 sm:px-8">
                <h3 class="text-sm font-semibold text-white">Why your score matters</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">
                    Landlords use payment history to assess reliability. A strong score shows you pay on time and take your tenancy seriously.
                </p>
            </div>
            <div class="bg-navy-900 px-6 py-6 sm:px-8">
                <h3 class="text-sm font-semibold text-white">How to improve</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $profile->portalImprovementFocus() }}</p>
                <ul class="mt-4 space-y-2">
                    @foreach ($profile->portalMaintainActions() as $action)
                        <li class="flex gap-2 text-sm text-slate-400">
                            <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-accent-teal" aria-hidden="true"></span>
                            {{ $action }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
