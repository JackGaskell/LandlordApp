@props(['items'])

<section class="rounded-2xl border border-white/[0.08] bg-navy-900/80 shadow-card-dark">
    <div class="border-b border-white/[0.06] px-6 py-4">
        <h3 class="text-sm font-semibold text-white">Your payments</h3>
        <p class="mt-0.5 text-xs text-slate-500">A simple record of each month</p>
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
                            <span class="rounded-full bg-brand-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-brand-300">This month</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $item->amountFormatted() }} · {{ $item->subtitle }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <x-payment-status-badge :status="$item->status" portal />
                </div>
            </div>
        @empty
            <div class="px-6 py-10 text-center">
                <p class="text-sm font-medium text-slate-300">No payments yet</p>
                <p class="mt-1 text-sm text-slate-500">Each month you pay will show up here — your progress, at a glance.</p>
            </div>
        @endforelse
    </div>
</section>
