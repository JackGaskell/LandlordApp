@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    @if (isset($icon))
        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-gradient-soft ring-1 ring-brand-500/10">
            <div class="text-brand-600 text-accent-teal">{{ $icon }}</div>
        </div>
    @endif
    <h3 class="text-sm font-semibold text-white">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm leading-relaxed text-slate-400">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-5">{{ $action }}</div>
    @endif
</div>
