@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'ui-btn-primary',
        'secondary' => 'ui-btn-secondary',
        'ghost' => 'ui-btn-ghost',
        'danger' => 'ui-btn-danger',
    ];
    $sizes = [
        'sm' => 'px-3 py-2 text-xs rounded-lg',
        'md' => '',
        'lg' => 'px-5 py-3 text-base',
    ];
    $classes = ($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
