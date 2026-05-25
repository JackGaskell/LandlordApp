<x-tenant-portal-layout :title="'Hi, '.$tenant->firstName()">
    <div class="mx-auto max-w-5xl space-y-5">
        <p class="-mt-1 text-sm text-slate-500">
            Your <span class="text-gradient font-medium">tenant score</span> reflects how consistently you pay rent.
        </p>

        <x-portal.score-experience :profile="$snapshot->reliability" />

        <x-portal.score-stats-strip :profile="$snapshot->reliability" />

        <x-portal.payment-action-card
            :upcoming="$snapshot->upcomingRent"
            :status="$snapshot->paymentStatus"
            :profile="$snapshot->reliability"
            :payment-id="$snapshot->collection->paymentId"
            :payment="$snapshot->currentPayment"
            :pay-online-coming-soon="$snapshot->payOnlineComingSoon"
        />

        <x-portal.payment-history :items="$snapshot->paymentHistory" />
    </div>
</x-tenant-portal-layout>
