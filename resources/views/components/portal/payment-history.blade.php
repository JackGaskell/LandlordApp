@props(['items'])

<x-ui.card title="Payment history" description="Your recent rent periods" :padding="false">
    <div class="divide-y divide-white/[0.06]">
        @forelse ($items as $item)
            <div @class([
                'flex items-center justify-between gap-4 px-5 py-4',
                'bg-brand-500/[0.04]' => $item->isCurrentPeriod,
            ])>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-white">{{ $item->periodLabel }}</p>
                        @if ($item->isCurrentPeriod)
                            <x-ui.badge tone="brand">Current</x-ui.badge>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">{{ $item->amountFormatted() }} · {{ $item->subtitle }}</p>
                </div>
                <div class="text-right">
                    <x-payment-status-badge :status="$item->status" />
                    @if ($item->paidAt)
                        <p class="mt-1 text-xs text-slate-500">Paid {{ $item->paidAt->format('j M Y') }}</p>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state
                title="No history yet"
                description="Your rent periods will appear here as they are recorded."
            />
        @endforelse
    </div>
</x-ui.card>
