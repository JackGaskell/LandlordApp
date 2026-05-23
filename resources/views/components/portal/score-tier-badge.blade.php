@props(['tier'])

@php
    $styles = [
        'excellent' => 'bg-brand-gradient-soft text-brand-200 ring-brand-400/25',
        'trusted' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/25',
        'reliable' => 'bg-brand-500/15 text-brand-200 ring-brand-400/20',
        'improving' => 'bg-white/[0.06] text-slate-200 ring-white/10',
        'attention' => 'bg-amber-500/10 text-amber-200 ring-amber-500/20',
    ];
    $style = $styles[$tier->portalTone()] ?? $styles['improving'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-4 py-1.5 text-sm font-semibold ring-1 {$style}"]) }}>
    {{ $tier->label() }}
</span>
