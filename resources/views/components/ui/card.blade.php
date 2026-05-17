@props(['title' => null, 'description' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => 'ui-card overflow-hidden']) }}>
    @if ($title || $description || isset($actions))
        <div class="flex items-center justify-between gap-4 border-b border-white/[0.06] px-6 py-4">
            <div>
                @if ($title)
                    <h3 class="text-sm font-semibold text-white">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="mt-0.5 text-xs text-slate-400">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div @class(['px-6 py-4' => $padding])>
        {{ $slot }}
    </div>
</div>
