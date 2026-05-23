@props([
    'upcoming',
    'status',
    'paymentId' => null,
    'payment' => null,
    'payOnlineComingSoon' => true,
])

<section @class([
    'relative overflow-hidden rounded-2xl border shadow-card-dark',
    'border-amber-500/25 bg-navy-900' => $upcoming->isOverdue,
    'border-white/[0.08] bg-navy-900' => ! $upcoming->isOverdue,
])>
    <div class="pointer-events-none absolute inset-0 opacity-40" aria-hidden="true">
        <div @class([
            'absolute -right-12 -top-12 h-40 w-40 rounded-full blur-3xl',
            'bg-amber-500/20' => $upcoming->isOverdue,
            'bg-brand-500/15' => ! $upcoming->isOverdue,
        ])></div>
    </div>

    <div class="relative p-6 sm:p-8">
        <p class="text-sm font-medium text-slate-400">
            {{ $upcoming->isOverdue ? 'Payment due' : 'Next payment' }}
        </p>

        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-4xl font-bold tabular-nums tracking-tight text-white sm:text-5xl">{{ $upcoming->amountFormatted() }}</p>
                <p @class([
                    'mt-2 text-sm font-medium',
                    'text-amber-300' => $upcoming->isOverdue,
                    'text-brand-300' => ! $upcoming->isOverdue,
                ])>{{ $upcoming->dueLabel }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $upcoming->dueDate->format('l, j F Y') }}</p>
            </div>

            @if ($status->canPayOnline)
                <x-ui.button href="#" size="lg" class="shrink-0 justify-center sm:min-w-[180px]">
                    Pay rent
                </x-ui.button>
            @elseif ($status->canUploadProof)
                <x-ui.button
                    type="button"
                    size="lg"
                    class="shrink-0 justify-center sm:min-w-[180px]"
                    x-data
                    @click="$refs.proofForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' })"
                >
                    Confirm payment
                </x-ui.button>
            @endif
        </div>

        @if ($status->headline && ! $status->canUploadProof)
            <p class="mt-5 text-sm text-slate-400">{{ $status->message }}</p>
        @elseif ($status->canUploadProof)
            <p class="mt-5 text-sm leading-relaxed text-slate-400">
                {{ $upcoming->isOverdue
                    ? 'Pay on time to protect your score. Upload proof once you\'ve sent your rent.'
                    : 'On-time payments strengthen your record. Confirm when you\'ve paid.' }}
            </p>
        @elseif ($payOnlineComingSoon && ! $status->canPayOnline)
            <p class="mt-5 text-xs text-slate-500">Online payments coming soon.</p>
        @endif

        @if ($status->canUploadProof)
            <div class="mt-6 border-t border-white/[0.06] pt-6" x-ref="proofForm">
                <x-portal.proof-upload :payment-id="$paymentId" :payment="$payment" />
            </div>
        @endif
    </div>
</section>
