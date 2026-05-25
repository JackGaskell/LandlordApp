@props([
    'upcoming',
    'status',
    'profile' => null,
    'paymentId' => null,
    'payment' => null,
    'payOnlineComingSoon' => true,
])

@php
    $hasPayment = $paymentId !== null;
    $primaryAction = $status->portalPrimaryAction();
    $showUploadOnLoad = $errors->any() && $status->canUploadProof;
@endphp

<section
    {{ $attributes->merge(['class' => 'flex h-full flex-col rounded-2xl bg-white/[0.03] p-5 sm:p-6']) }}
    x-data="{ showUpload: @js($showUploadOnLoad) }"
    @class([
        'ring-1 ring-white/[0.06]' => ! ($hasPayment && $upcoming->isOverdue) && ! $status->portalIsPaid(),
        'ring-1 ring-amber-500/20' => $hasPayment && $upcoming->isOverdue,
        'ring-1 ring-emerald-500/15' => $status->portalIsPaid(),
    ])
>
    @if ($hasPayment)
        <div class="flex items-start justify-between gap-3">
            <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Next payment</p>
            <x-ui.badge :tone="$status->portalStatusTone()">{{ $status->portalStatusLabel() }}</x-ui.badge>
        </div>

        <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-white">
            {{ $upcoming->amountFormatted() }}
        </p>

        @if ($status->portalIsPaid())
            <p class="mt-2 text-sm text-emerald-300">
                @if ($payment?->paid_at)
                    Paid {{ $payment->paid_at->format('j M') }}
                @else
                    Recorded
                @endif
            </p>
            @if ($profile && ($recordedMessage = $profile->portalPaymentRecordedMessage()))
                <p class="mt-1 text-xs leading-relaxed text-emerald-300/80">{{ $recordedMessage }}</p>
            @endif
        @else
            <p @class([
                'mt-2 text-sm font-medium',
                'text-amber-300/90' => $upcoming->isOverdue,
                'text-slate-300' => ! $upcoming->isOverdue,
            ])>{{ $upcoming->dueLabel }}</p>
            <p class="text-xs text-slate-500">{{ $upcoming->portalDueDateLong() }}</p>

            @if ($profile)
                <div class="mt-3 space-y-1 border-t border-white/[0.06] pt-3">
                    @if ($protectionMessage = $profile->portalPaymentProtectionMessage())
                        <p class="text-xs text-slate-400">{{ $protectionMessage }}</p>
                    @endif
                    <p class="text-xs text-slate-400">{{ $profile->portalProjectedScoreOnTimeLabel() }}</p>
                    <p class="text-xs text-slate-500">{{ $profile->portalProjectedScoreLateLabel() }}</p>
                </div>
            @endif
        @endif

        @if ($status->portalIsActionable())
            <div class="mt-auto pt-4">
                @if ($primaryAction === 'pay')
                    <form method="POST" action="{{ route('portal.payments.checkout', $paymentId) }}">
                        @csrf
                        <x-ui.button type="submit" class="w-full justify-center shadow-glow">
                            {{ $status->portalPrimaryActionLabel() }}
                        </x-ui.button>
                    </form>
                    @if ($status->canUploadProof)
                        <button
                            type="button"
                            class="mt-3 w-full text-center text-xs font-medium text-slate-500 hover:text-white"
                            @click="showUpload = ! showUpload"
                            x-text="showUpload ? @js($status->portalUploadToggleHide()) : @js($status->portalUploadToggleShow())"
                        ></button>
                    @endif
                @else
                    <x-ui.button
                        type="button"
                        class="w-full justify-center shadow-glow"
                        @click="showUpload = ! showUpload"
                        x-show="! showUpload"
                    >
                        {{ $status->portalPrimaryActionLabel() }}
                    </x-ui.button>
                    @if ($profile)
                        <p class="mt-2 text-center text-xs text-slate-500">{{ $profile->portalPrimaryActionSubtext() }}</p>
                    @endif
                @endif

                @if ($status->canUploadProof)
                    <div
                        x-show="showUpload"
                        x-transition
                        x-cloak
                        class="mt-4 rounded-xl bg-navy-950/50 p-4 ring-1 ring-white/[0.06]"
                    >
                        <x-portal.proof-upload :payment-id="$paymentId" :payment="$payment" compact />
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="flex flex-1 flex-col justify-center py-4">
            <p class="text-sm font-semibold text-white">All caught up</p>
            <p class="mt-1 text-xs text-slate-500">No payment due right now.</p>
        </div>
    @endif
</section>
