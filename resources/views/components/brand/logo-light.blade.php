@props([
    'size' => 'md',
    'showTagline' => false,
    'href' => null,
])

@php
    $textSizes = [
        'sm' => 'text-sm',
        'md' => 'text-base',
        'lg' => 'text-lg',
    ];
    $href = $href ?? (auth()->check() ? route('dashboard') : url('/'));
@endphp

<a {{ $attributes->merge(['href' => $href, 'class' => 'group inline-flex items-center gap-3', 'aria-label' => config('app.name')]) }}>
    <x-brand.logo-icon :size="$size" class="transition-transform duration-200 group-hover:scale-105" />

    <div class="min-w-0 leading-tight">
        <p class="{{ $textSizes[$size] ?? $textSizes['md'] }} font-semibold tracking-tight text-white">
            <x-brand.wordmark />
        </p>
        @if ($showTagline)
            <p class="text-[11px] font-medium text-slate-400">Rent collection intelligence</p>
        @endif
    </div>
</a>
