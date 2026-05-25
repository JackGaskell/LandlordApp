<x-app-layout
    :title="'Review · '.$proof->tenant->name"
    description="Review the tenant's payment confirmation and confirm or decline it."
>
    <x-slot name="actions">
        <x-ui.button variant="secondary" :href="route('payment-proofs.index')">Back to confirmations</x-ui.button>
        <x-ui.button variant="secondary" :href="route('payment-proofs.file', $proof)" target="_blank">View file</x-ui.button>
    </x-slot>

    <x-ui.flash />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Submission details">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Tenant</dt>
                        <dd class="mt-1 text-sm font-medium text-white">
                            <a href="{{ route('tenants.show', $proof->tenant) }}" class="text-brand-300 hover:text-white">{{ $proof->tenant->name }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Status</dt>
                        <dd class="mt-1"><x-payment-proof-status-badge :status="$proof->status" /></dd>
                    </div>
                    @if ($proof->paymentHistory)
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Rent period</dt>
                            <dd class="mt-1 text-sm text-white">{{ $proof->paymentHistory->due_date->format('F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Amount</dt>
                            <dd class="mt-1 text-sm text-white">£{{ number_format($proof->paymentHistory->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Payment verification</dt>
                            <dd class="mt-1">
                                <x-payment-verification-badge :status="$proof->paymentHistory->verification_status" />
                            </dd>
                        </div>
                    @endif
                    @if ($proof->tenant_marked_paid)
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Tenant says paid on</dt>
                            <dd class="mt-1 text-sm text-white">{{ $proof->claimed_paid_at?->format('j M Y') ?? '—' }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Submitted</dt>
                        <dd class="mt-1 text-sm text-white">{{ $proof->created_at->format('j M Y, H:i') }}</dd>
                    </div>
                </dl>

                @if ($proof->tenant_note)
                    <div class="mt-6 rounded-xl border border-white/[0.08] bg-white/[0.03] p-4">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Tenant note</p>
                        <p class="mt-2 text-sm text-slate-300">{{ $proof->tenant_note }}</p>
                    </div>
                @endif

                @if ($proof->landlord_note && $proof->status->isFinal())
                    <div class="mt-4 rounded-xl border border-white/[0.08] bg-white/[0.03] p-4">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Your note</p>
                        <p class="mt-2 text-sm text-slate-300">{{ $proof->landlord_note }}</p>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <div class="space-y-6">
            @if ($proof->isPending())
                <x-ui.card title="Review" description="Confirm only if the receipt matches the rent payment.">
                    <form method="POST" action="{{ route('payment-proofs.approve', $proof) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="approve_note" class="text-sm font-medium text-slate-300">Note (optional)</label>
                            <textarea id="approve_note" name="landlord_note" rows="2" class="ui-input mt-1.5 w-full" placeholder="e.g. Received — thank you"></textarea>
                        </div>
                        <x-ui.button type="submit" class="w-full justify-center">Confirm payment</x-ui.button>
                    </form>

                    <form method="POST" action="{{ route('payment-proofs.reject', $proof) }}" class="mt-6 space-y-4 border-t border-white/[0.06] pt-6">
                        @csrf
                        <div>
                            <label for="reject_note" class="text-sm font-medium text-slate-300">Reason (optional)</label>
                            <textarea id="reject_note" name="landlord_note" rows="2" class="ui-input mt-1.5 w-full" placeholder="Explain what you need from the tenant"></textarea>
                        </div>
                        <x-ui.button type="submit" variant="secondary" class="w-full justify-center">Decline</x-ui.button>
                    </form>
                </x-ui.card>
            @else
                <x-ui.card title="Reviewed">
                    <p class="text-sm text-slate-400">
                        {{ $proof->reviewed_at?->format('j M Y, H:i') }}
                        @if ($proof->reviewedBy)
                            by {{ $proof->reviewedBy->name }}
                        @endif
                    </p>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-app-layout>
