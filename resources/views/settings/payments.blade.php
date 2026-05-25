<x-app-layout
    title="Receive rent payments"
    description="Connect Stripe so tenants pay you directly. We never hold your rent money."
>
    <x-ui.flash />

    <x-ui.card class="max-w-2xl">
        @if (! $connectEnabled)
            <p class="text-sm text-slate-400">
                Card payments are not enabled on this environment yet. Tenants can still confirm bank transfers with a receipt.
            </p>
        @elseif ($landlord->canAcceptStripeRentPayments())
            <div class="flex items-start gap-3">
                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-white">Stripe connected</p>
                    <p class="mt-1 text-sm text-slate-400">
                        When tenants tap <strong class="text-slate-300">Pay now</strong>, funds go straight to your Stripe account. {{ config('app.name') }} does not store rent payments.
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('settings.stripe.connect') }}" class="mt-6">
                @csrf
                <x-ui.button type="submit" variant="secondary">Update Stripe details</x-ui.button>
            </form>
        @else
            <p class="text-sm text-slate-400">
                Connect a free Stripe Express account to accept card payments from tenants. Rent is charged on <strong class="text-slate-300">your</strong> account — we are not the merchant and we do not hold your money.
            </p>
            <ul class="mt-4 space-y-2 text-sm text-slate-500">
                <li>Tenants see <strong class="text-slate-400">Pay now</strong> on their portal when you are connected.</li>
                <li>Payments still update scores, reminders, and rent periods automatically.</li>
                <li>Stripe handles payouts to your bank per their schedule.</li>
            </ul>
            <form method="POST" action="{{ route('settings.stripe.connect') }}" class="mt-6">
                @csrf
                <x-ui.button type="submit">Connect Stripe</x-ui.button>
            </form>
        @endif
    </x-ui.card>
</x-app-layout>
