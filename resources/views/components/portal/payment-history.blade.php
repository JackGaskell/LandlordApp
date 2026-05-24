@props(['items'])

<section>
    <div class="mb-3 flex items-baseline justify-between gap-2">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">History</h3>
        <span class="text-[10px] text-slate-600">Last {{ min(4, $items->count()) }} months</span>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white/[0.03] ring-1 ring-white/[0.06]">
        @forelse ($items->take(4) as $item)
            <div @class([
                'flex items-center justify-between gap-3 border-b border-white/[0.04] px-4 py-3 last:border-b-0',
                'bg-brand-500/[0.04]' => $item->isCurrentPeriod,
            ])>
                <div class="min-w-0 flex items-center gap-3">
                    <p class="w-16 shrink-0 text-xs font-medium text-slate-400">{{ $item->dueDate->format('M Y') }}</p>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-white">{{ $item->amountFormatted() }}</p>
                        @if ($item->isCurrentPeriod)
                            <p class="text-[10px] text-brand-300">This month</p>
                        @endif
                    </div>
                </div>
                <x-payment-status-badge :status="$item->status" portal class="!text-[10px]" />
            </div>
        @empty
            <p class="px-4 py-8 text-center text-sm text-slate-500">Payments will appear here as you go.</p>
        @endforelse
    </div>
</section>
