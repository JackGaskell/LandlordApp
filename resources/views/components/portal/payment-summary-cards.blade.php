@props(['cards'])

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($cards as $card)
        <div class="rounded-xl border border-white/[0.08] bg-navy-900/80 p-5 shadow-card-dark">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $card->label }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-white">{{ $card->value }}</p>
            @if ($card->hint)
                <p class="mt-1 text-xs text-slate-500">{{ $card->hint }}</p>
            @endif
        </div>
    @endforeach
</div>
