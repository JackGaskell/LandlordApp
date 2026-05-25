@props(['status', 'paymentId' => null, 'payment' => null, 'payOnlineComingSoon' => true])

<div class="ui-card-elevated h-full p-6">
    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Payment status</p>
    <p class="mt-3 text-xl font-semibold text-white">{{ $status->headline }}</p>
    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $status->message }}</p>

    @if ($status->canUploadProof)
        <div class="mt-6 border-t border-white/[0.06] pt-6">
            <x-portal.proof-upload :payment-id="$paymentId" :payment="$payment" />
        </div>
    @endif

    @if ($status->canPayOnline && $paymentId)
        <div class="mt-4">
            <form method="POST" action="{{ route('portal.payments.checkout', $paymentId) }}">
                @csrf
                <x-ui.button type="submit" class="w-full justify-center">Pay rent online</x-ui.button>
            </form>
        </div>
    @elseif ($payOnlineComingSoon)
        <p class="mt-6 text-xs text-slate-500">Online card payments are coming soon — you can still upload a receipt today.</p>
    @endif
</div>
