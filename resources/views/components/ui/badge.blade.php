@props([
    'tone' => 'neutral',
])

@php
    $tones = [
        'neutral' => 'bg-slate-100 text-slate-700 ring-slate-200/80',
        'success' => 'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20',
        'warning' => 'bg-amber-500/10 text-amber-800 ring-amber-500/20',
        'danger' => 'bg-rose-500/10 text-rose-700 ring-rose-500/20',
        'info' => 'bg-brand-500/10 text-brand-700 ring-brand-500/20 text-accent-teal',
        'brand' => 'bg-brand-gradient-soft text-brand-700 ring-brand-500/15 text-accent-teal',
    ];
    $classes = $tones[$tone] ?? $tones['neutral'];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$classes,
]) }}>
    {{ $slot }}
</span>
