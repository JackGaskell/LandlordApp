<x-tenant-portal-layout
    :title="'Hi, '.$tenant->firstName()"
    description="Stay on top of rent and watch your score grow."
>
    <div class="mx-auto max-w-2xl space-y-8">
        <x-portal.score-experience :profile="$snapshot->reliability" />

        <x-portal.payment-action-card
            :upcoming="$snapshot->upcomingRent"
            :status="$snapshot->paymentStatus"
            :payment-id="$snapshot->collection->paymentId"
            :payment="$snapshot->currentPayment"
            :pay-online-coming-soon="$snapshot->payOnlineComingSoon"
        />

        <x-portal.payment-history :items="$snapshot->paymentHistory" />
    </div>
</x-tenant-portal-layout>
