@props(['items'])

<section class="rounded-2xl border border-white/[0.08] bg-navy-900/80 shadow-card-dark">
    <div class="border-b border-white/[0.06] px-6 py-4">
        <h3 class="text-sm font-semibold text-white">Recent payments</h3>
    </div>

    <div class="divide-y divide-white/[0.06]">
        @forelse ($items->take(6) as $item)
            <div @class([
                'flex items-center justify-between gap-4 px-6 py-4',
                'bg-brand-500/[0.03]' => $item->isCurrentPeriod,
            ])>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-white">{{ $item->periodLabel }}</p>
                        @if ($item->isCurrentPeriod)
                            <span class="rounded-full bg-brand-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-brand-300">Current</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $item->amountFormatted() }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <x-payment-status-badge :status="$item->status" />
                    @if ($item->paidAt)
                        <p class="mt-1 text-xs text-slate-500">{{ $item->paidAt->format('j M Y') }}</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-6 py-10 text-center">
                <p class="text-sm text-slate-400">Your payment history will appear here.</p>
            </div>
        @endforelse
    </div>
</section>
