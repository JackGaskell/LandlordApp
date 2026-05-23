@props([
    'upcoming',
    'status',
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
    x-data="{ showUpload: @js($showUploadOnLoad) }"
    @class([
        'overflow-hidden rounded-3xl border bg-navy-900 shadow-card-dark',
        'border-amber-500/20' => $hasPayment && $upcoming->isOverdue,
        'border-emerald-500/15' => $status->portalIsPaid(),
        'border-white/[0.08]' => $hasPayment && ! $upcoming->isOverdue && ! $status->portalIsPaid(),
        'border-white/[0.06]' => ! $hasPayment,
    ])
>
    @if ($hasPayment)
        <div class="px-6 pt-6 sm:px-8 sm:pt-8">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-slate-400">{{ $upcoming->portalPeriodLabel() }}</p>
                <x-ui.badge :tone="$status->portalStatusTone()">{{ $status->portalStatusLabel() }}</x-ui.badge>
            </div>

            <p class="mt-4 text-5xl font-bold tabular-nums tracking-tight text-white sm:text-6xl">
                {{ $upcoming->amountFormatted() }}
            </p>

            @if ($status->portalIsPaid())
                <p class="mt-3 text-sm text-emerald-300">
                    @if ($payment?->paid_at)
                        Recorded {{ $payment->paid_at->format('j M Y') }}
                    @else
                        Marked as paid
                    @endif
                </p>
                <p class="mt-1 text-sm text-slate-400">{{ $status->portalNextStep() }}</p>
            @else
                <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <p @class([
                        'text-base font-medium',
                        'text-amber-300' => $upcoming->isOverdue,
                        'text-white' => ! $upcoming->isOverdue,
                    ])>{{ $upcoming->dueLabel }}</p>
                    <span class="text-slate-600" aria-hidden="true">·</span>
                    <p class="text-sm text-slate-500">{{ $upcoming->portalDueDateLong() }}</p>
                </div>
                <p class="mt-3 text-sm text-slate-400">{{ $status->portalNextStep() }}</p>
            @endif
        </div>

        @if ($status->portalIsActionable())
            <div class="mt-8 px-6 pb-6 sm:px-8 sm:pb-8">
                @if ($primaryAction === 'pay')
                    <x-ui.button href="#" size="xl" class="justify-center shadow-glow">
                        {{ $status->portalPrimaryActionLabel() }}
                    </x-ui.button>

                    @if ($status->canUploadProof)
                        <button
                            type="button"
                            class="mt-4 w-full text-center text-sm font-medium text-slate-400 transition-colors hover:text-white"
                            @click="showUpload = ! showUpload"
                            x-text="showUpload ? @js($status->portalUploadToggleHide()) : @js($status->portalUploadToggleShow())"
                        ></button>
                    @endif
                @else
                    <x-ui.button
                        type="button"
                        size="xl"
                        class="justify-center shadow-glow"
                        @click="showUpload = ! showUpload"
                        x-show="! showUpload"
                    >
                        {{ $status->portalPrimaryActionLabel() }}
                    </x-ui.button>

                    @if ($payOnlineComingSoon)
                        <p class="mt-4 text-center text-xs text-slate-500">Pay by card — coming soon</p>
                    @endif
                @endif

                @if ($status->canUploadProof)
                    <div
                        x-show="showUpload"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        x-cloak
                        class="mt-6 rounded-2xl border border-white/[0.06] bg-white/[0.02] p-5 sm:p-6"
                    >
                        <p class="mb-5 text-sm text-slate-400">A quick photo or PDF is enough — we will update your record once it is reviewed.</p>
                        <x-portal.proof-upload :payment-id="$paymentId" :payment="$payment" />
                    </div>
                @endif
            </div>
        @else
            <div class="h-2"></div>
        @endif
    @else
        <div class="px-6 py-10 text-center sm:px-8">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5" />
                </svg>
            </div>
            <p class="mt-4 text-lg font-semibold text-white">You are all caught up</p>
            <p class="mt-2 text-sm text-slate-400">Nothing to pay right now. We will let you know when the next month is ready.</p>
        </div>
    @endif
</section>
