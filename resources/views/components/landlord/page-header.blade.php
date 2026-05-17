@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <h1 class="text-2xl font-semibold text-white">{{ $title }}</h1>
    @if ($description)
        <p class="mt-1 text-sm text-slate-400">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-4 flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
