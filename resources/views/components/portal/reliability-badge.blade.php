@props(['profile'])

@php
    $tones = [
        'new' => 'bg-slate-500/15 text-slate-300 ring-slate-500/20',
        'bronze' => 'bg-amber-600/15 text-amber-300 ring-amber-500/25',
        'silver' => 'bg-slate-400/15 text-slate-200 ring-slate-400/25',
        'gold' => 'bg-yellow-500/15 text-yellow-200 ring-yellow-500/30',
        'platinum' => 'bg-brand-500/15 text-brand-200 ring-brand-400/30',
    ];
    $tone = $tones[$profile->badge->value] ?? $tones['new'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 {$tone}"]) }}>
    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
    </svg>
    {{ $profile->badge->label() }}
</span>
