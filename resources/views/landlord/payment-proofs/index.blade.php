<x-app-layout
    title="Payment proofs"
    description="Review tenant payment submissions and confirm rent without chasing."
>
    <x-slot name="actions">
        <x-ui.button variant="secondary" :href="route('payment-proofs.index')">All</x-ui.button>
        <x-ui.button variant="secondary" :href="route('payment-proofs.index', ['status' => 'pending'])">
            Pending @if ($pendingCount > 0)<span class="ml-1 rounded-full bg-brand-500/30 px-1.5 text-xs">{{ $pendingCount }}</span>@endif
        </x-ui.button>
    </x-slot>

    <x-ui.flash />

    <x-ui.card :padding="false">
        <div class="divide-y divide-white/[0.06]">
            @forelse ($proofs as $proof)
                <a
                    href="{{ route('payment-proofs.show', $proof) }}"
                    class="flex flex-col gap-3 px-5 py-4 transition hover:bg-white/[0.03] sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="min-w-0">
                        <p class="font-medium text-white">{{ $proof->tenant->name }}</p>
                        <p class="mt-0.5 text-sm text-slate-400">
                            @if ($proof->paymentHistory)
                                {{ $proof->paymentHistory->due_date->format('F Y') }} · £{{ number_format($proof->paymentHistory->amount, 2) }}
                            @else
                                General submission
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-500">{{ $proof->created_at->diffForHumans() }} · {{ $proof->original_filename }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <x-payment-proof-status-badge :status="$proof->status" />
                        <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </a>
            @empty
                <x-ui.empty-state
                    title="No submissions yet"
                    description="When tenants upload payment proof from their portal, they will appear here for review."
                />
            @endforelse
        </div>

        @if ($proofs->hasPages())
            <div class="border-t border-white/[0.06] px-5 py-4">
                {{ $proofs->links() }}
            </div>
        @endif
    </x-ui.card>
</x-app-layout>
