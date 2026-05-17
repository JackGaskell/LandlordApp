@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default',
])

@php
    $tones = [
        'default' => [
            'ring' => 'ring-white/[0.06]',
            'icon' => 'bg-white/[0.06] text-slate-400',
            'value' => 'text-white',
        ],
        'success' => [
            'ring' => 'ring-emerald-500/25',
            'icon' => 'bg-emerald-500/10 text-emerald-400',
            'value' => 'text-emerald-400',
        ],
        'warning' => [
            'ring' => 'ring-amber-500/25',
            'icon' => 'bg-amber-500/10 text-amber-400',
            'value' => 'text-amber-400',
        ],
        'danger' => [
            'ring' => 'ring-rose-500/25',
            'icon' => 'bg-rose-500/10 text-rose-400',
            'value' => 'text-rose-400',
        ],
        'brand' => [
            'ring' => 'ring-brand-500/20',
            'icon' => 'bg-brand-gradient-soft text-accent-teal',
            'value' => 'text-white',
        ],
    ];
    $t = $tones[$tone] ?? $tones['default'];
@endphp

<div {{ $attributes->merge(['class' => "ui-card-elevated ring-1 p-5 {$t['ring']}"]) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight {{ $t['value'] }}">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
            @endif
        </div>
        @if (isset($icon))
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $t['icon'] }}">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>
