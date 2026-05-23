@props(['collection'])

<div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-navy-900 p-6 shadow-card-dark sm:p-8">
    <div class="pointer-events-none absolute inset-0 opacity-40" aria-hidden="true">
        <div @class([
            'absolute -right-16 -top-16 h-48 w-48 rounded-full blur-3xl',
            'bg-emerald-500/20' => $collection->tone === 'success',
            'bg-brand-500/25' => $collection->tone === 'brand',
            'bg-amber-500/20' => $collection->tone === 'warning',
            'bg-slate-500/10' => ! in_array($collection->tone, ['success', 'brand', 'warning'], true),
        ])></div>
    </div>

    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Collection status</p>
                <x-ui.badge :tone="$collection->tone">{{ $collection->status->label() }}</x-ui.badge>
            </div>
            <p class="mt-3 text-2xl font-semibold tracking-tight text-white sm:text-3xl">{{ $collection->headline }}</p>
            <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-400">{{ $collection->message }}</p>
        </div>

        @if ($collection->amount !== null)
            <div class="shrink-0 rounded-xl border border-white/[0.08] bg-white/[0.03] px-5 py-4 text-right">
                <p class="text-xs text-slate-500">{{ $collection->isOverdue ? 'Amount outstanding' : 'Current period' }}</p>
                <p class="mt-1 text-2xl font-semibold text-white">{{ $collection->amountFormatted() }}</p>
                @if ($collection->dueDateFormatted())
                    <p class="mt-1 text-xs text-slate-500">Due {{ $collection->dueDateFormatted() }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
