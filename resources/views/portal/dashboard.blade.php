<x-tenant-portal-layout
    :title="'Hi, '.$tenant->firstName()"
    description="Your rent health at a glance — build your score, streak, and consistency."
>
    <div class="space-y-6">
        <x-portal.collection-status :collection="$snapshot->collection" />

        <x-portal.payment-summary-cards :cards="$snapshot->summaryCards" />

        <x-portal.upcoming-rent :upcoming="$snapshot->upcomingRent" />

        <x-portal.reliability-hero :profile="$snapshot->reliability" />

        <div class="grid gap-6 lg:grid-cols-2">
            <x-portal.streak-counter :profile="$snapshot->reliability" />
            <x-portal.consistency-meter :profile="$snapshot->reliability" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-portal.payment-status
                :status="$snapshot->paymentStatus"
                :payment-id="$snapshot->collection->paymentId"
                :payment="$snapshot->currentPayment"
                :pay-online-coming-soon="$snapshot->payOnlineComingSoon"
            />
            <x-portal.payment-timeline :timeline="$snapshot->reliability->timeline" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-portal.payment-history :items="$snapshot->paymentHistory" />
            <x-portal.recent-activity :items="$snapshot->recentActivity" />
        </div>
    </div>
</x-tenant-portal-layout>
