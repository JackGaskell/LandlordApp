@props(['profile'])

@php
    $stats = $profile->portalCompactStats();
@endphp

<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-3 sm:grid-cols-4']) }}>
    @foreach ($stats as $stat)
        <div class="rounded-xl bg-navy-900/90 p-4 ring-1 ring-white/[0.06]">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-1.5 text-xl font-bold tabular-nums text-white sm:text-2xl">{{ $stat['value'] }}</p>
                    @if (! empty($stat['hint']))
                        <p class="mt-1.5 text-[10px] leading-snug text-slate-500">{{ $stat['hint'] }}</p>
                    @endif
                </div>
                <div @class([
                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg',
                    'bg-emerald-500/10 text-emerald-400' => $stat['icon'] === 'check',
                    'bg-amber-500/10 text-amber-400' => $stat['icon'] === 'clock',
                    'bg-orange-500/10 text-orange-400' => $stat['icon'] === 'flame' && $stat['tone'] === 'streak',
                    'bg-white/[0.06] text-slate-500' => $stat['icon'] === 'flame' && $stat['tone'] === 'default',
                    'bg-brand-gradient-soft text-accent-teal' => $stat['icon'] === 'chart',
                ])>
                    @switch($stat['icon'])
                        @case('check')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5" />
                            </svg>
                            @break
                        @case('clock')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            @break
                        @case('flame')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" />
                            </svg>
                            @break
                        @case('chart')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
    @endforeach
</div>
