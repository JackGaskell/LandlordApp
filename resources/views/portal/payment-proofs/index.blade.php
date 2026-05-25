<x-tenant-portal-layout title="Your confirmations" description="Receipts you've sent and their review status.">
    <div class="mb-6">
        <x-ui.button variant="secondary" :href="route('portal.dashboard')">Back home</x-ui.button>
    </div>

    <x-ui.card :padding="false">
        <div class="divide-y divide-white/[0.06]">
            @forelse ($proofs as $proof)
                <div class="px-5 py-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-white">
                                @if ($proof->paymentHistory)
                                    {{ $proof->paymentHistory->due_date->format('F Y') }}
                                @else
                                    Payment confirmation
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $proof->created_at->format('j M Y') }} · {{ $proof->original_filename }}</p>
                        </div>
                        <x-payment-proof-status-badge :status="$proof->status" :for-tenant="true" />
                    </div>
                    @if ($proof->tenant_note)
                        <p class="mt-2 text-sm text-slate-400">{{ $proof->tenant_note }}</p>
                    @endif
                    @if ($proof->landlord_note && $proof->status->isFinal())
                        <p class="mt-2 rounded-lg bg-white/[0.03] px-3 py-2 text-sm text-slate-300">
                            <span class="text-xs font-medium text-slate-500">Note:</span> {{ $proof->landlord_note }}
                        </p>
                    @endif
                    <a href="{{ route('portal.payment-proofs.file', $proof) }}" class="mt-3 inline-block text-xs font-medium text-brand-300 hover:text-white">View file</a>
                </div>
            @empty
                <x-ui.empty-state
                    title="Nothing sent yet"
                    description="When you upload a receipt from your home screen, it will show up here while it is reviewed."
                />
            @endforelse
        </div>
    </x-ui.card>
</x-tenant-portal-layout>
