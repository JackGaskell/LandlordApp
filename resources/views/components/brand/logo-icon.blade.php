@props([
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8',
        'md' => 'h-9 w-9',
        'lg' => 'h-11 w-11',
        'xl' => 'h-14 w-14',
    ];
    $gradientId = 'la-icon-grad-'.substr(md5((string) $attributes), 0, 8);
    $gradientRef = "url(#{$gradientId})";
@endphp

<svg
    {{ $attributes->merge(['class' => ($sizes[$size] ?? $sizes['md']).' shrink-0']) }}
    viewBox="0 0 40 40"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
>
    <defs>
        <linearGradient id="{{ $gradientId }}" x1="6" y1="34" x2="34" y2="6" gradientUnits="userSpaceOnUse">
            <stop stop-color="#3B82F6"/>
            <stop offset="0.5" stop-color="#2DD4BF"/>
            <stop offset="1" stop-color="#84CC16"/>
        </linearGradient>
    </defs>
    <rect width="40" height="40" rx="10" fill="{{ $gradientRef }}" fill-opacity="0.12"/>
    <path
        d="M20 9L11 16.5V31H16V22H24V31H29V16.5L20 9Z"
        fill="{{ $gradientRef }}"
        stroke="{{ $gradientRef }}"
        stroke-width="0.5"
        stroke-linejoin="round"
    />
    <rect x="24" y="12" width="3" height="5" rx="0.5" fill="{{ $gradientRef }}"/>
</svg>
