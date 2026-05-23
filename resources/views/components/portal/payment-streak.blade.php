@props(['streak'])

<div class="ui-card-elevated flex h-full flex-col p-6">
    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">On-time streak</p>

    <div class="mt-4 flex items-baseline gap-2">
        <p class="text-5xl font-semibold text-white">{{ $streak->currentMonths }}</p>
        <p class="text-sm text-slate-400">{{ str('month')->plural($streak->currentMonths) }} in a row</p>
    </div>

    <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-400">{{ $streak->message }}</p>

    @if ($streak->bestMonths > $streak->currentMonths)
        <p class="mt-4 border-t border-white/[0.06] pt-4 text-xs text-slate-500">
            Personal best: {{ $streak->bestMonths }} {{ str('month')->plural($streak->bestMonths) }}
        </p>
    @endif
</div>
