@props(['profile'])

@php
    $scoreProgress = $profile->portalScoreProgressPercent();
    $scoreDisplay = $profile->portalScoreDisplay();
    $scoreSubtitle = $profile->portalScoreSubtitle();
    $scoreLabelSize = $profile->portalScoreIsEstablished() ? 20 : 14;
@endphp

<section {{ $attributes->merge(['class' => 'relative rounded-2xl bg-navy-900/90 ring-1 ring-white/[0.06]']) }}>
    <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-2xl" aria-hidden="true">
        <div class="absolute inset-0 bg-brand-gradient-soft opacity-25"></div>
    </div>

    <div class="relative p-5 sm:p-6">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:gap-8">
            <div class="mx-auto w-48 shrink-0 sm:mx-0 sm:w-52 lg:w-56">
                <svg
                    class="block w-full"
                    viewBox="0 0 120 64"
                    role="img"
                    aria-label="{{ $profile->portalScoreAriaLabel() }}"
                >
                    <defs>
                        <linearGradient id="scoreArcGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#3b82f6" />
                            <stop offset="50%" stop-color="#2dd4bf" />
                            <stop offset="100%" stop-color="#84cc16" />
                        </linearGradient>
                    </defs>
                    <path
                        d="M 10 54 A 50 50 0 0 1 110 54"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="8"
                        stroke-linecap="round"
                        class="text-white/[0.08]"
                    />
                    @if ($profile->portalScoreIsEstablished())
                        <path
                            d="M 10 54 A 50 50 0 0 1 110 54"
                            fill="none"
                            stroke="url(#scoreArcGradient)"
                            stroke-width="8"
                            stroke-linecap="round"
                            pathLength="100"
                            stroke-dasharray="100"
                            stroke-dashoffset="{{ 100 - $scoreProgress }}"
                            class="transition-all duration-1000 ease-smooth"
                        />
                    @endif
                    <text
                        x="60"
                        y="36"
                        text-anchor="middle"
                        fill="#ffffff"
                        font-family="Inter, ui-sans-serif, system-ui, sans-serif"
                        font-size="{{ $scoreLabelSize }}"
                        font-weight="700"
                    >{{ $scoreDisplay }}</text>
                    <text
                        x="60"
                        y="47"
                        text-anchor="middle"
                        fill="#64748b"
                        font-family="Inter, ui-sans-serif, system-ui, sans-serif"
                        font-size="8"
                    >{{ $scoreSubtitle }}</text>
                </svg>
            </div>

            <div class="min-w-0 w-full flex-1">
                <x-portal.score-progression :profile="$profile" />
            </div>
        </div>
    </div>
</section>
