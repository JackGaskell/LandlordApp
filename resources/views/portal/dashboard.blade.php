<x-tenant-portal-layout :title="'Hi, '.$tenant->firstName()">
    <div class="mx-auto max-w-5xl space-y-5">
        <p class="-mt-1 text-sm text-slate-500">
            Maintain and grow your <span class="text-gradient font-medium">tenant score</span>
        </p>

        <div class="grid gap-4 lg:grid-cols-5 lg:items-start lg:gap-5">
            <div class="flex min-h-0 flex-col gap-4 lg:col-span-3">
                <x-portal.score-experience :profile="$snapshot->reliability" class="w-full" />
                <x-portal.score-stats-strip :profile="$snapshot->reliability" />
            </div>

            <div class="flex min-h-0 lg:col-span-2">
                <x-portal.payment-action-card
                    class="w-full"
                    :upcoming="$snapshot->upcomingRent"
                    :status="$snapshot->paymentStatus"
                    :payment-id="$snapshot->collection->paymentId"
                    :payment="$snapshot->currentPayment"
                    :pay-online-coming-soon="$snapshot->payOnlineComingSoon"
                />
            </div>
        </div>

        <x-portal.payment-history :items="$snapshot->paymentHistory" />
    </div>
</x-tenant-portal-layout>
