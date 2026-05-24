<?php

namespace App\Services\Portal;

use App\DataTransferObjects\Portal\TenantPortalActivityItem;
use App\DataTransferObjects\Portal\TenantPortalSnapshot;
use App\Enums\PaymentProofStatus;
use App\Models\PaymentProof;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Reliability\TenantReliabilityProfileService;
use Illuminate\Support\Collection;

class TenantPortalDashboardService
{
    public function __construct(
        protected TenantPaymentLifecycleService $lifecycle,
        protected TenantReliabilityProfileService $reliability,
    ) {}

    public function snapshot(Tenant $tenant): TenantPortalSnapshot
    {
        $this->lifecycle->refreshOpenPaymentStatuses($tenant);

        $tenant->loadMissing(['paymentHistories', 'paymentProofs']);

        $tenant->refresh();

        $currentPayment = $this->lifecycle->resolveFocusPayment($tenant);
        $reliability = $this->reliability->profile($tenant, preferCache: false);
        $collection = $this->lifecycle->buildCollectionSummary($tenant, $currentPayment);

        return new TenantPortalSnapshot(
            currentPayment: $currentPayment,
            collection: $collection,
            upcomingRent: $this->lifecycle->buildUpcomingRent($tenant, $currentPayment),
            paymentStatus: $this->lifecycle->buildPaymentStatus($currentPayment),
            reliability: $reliability,
            paymentHistory: $this->lifecycle->buildPaymentHistory($tenant),
            recentActivity: $this->recentActivity($tenant),
            summaryCards: $this->lifecycle->buildSummaryCards(
                $tenant,
                $collection,
                $reliability->currentStreak,
                $reliability->consistencyRate,
            ),
            payOnlineComingSoon: (bool) config('landlord.portal.pay_online_coming_soon', true),
        );
    }

    /**
     * @return Collection<int, TenantPortalActivityItem>
     */
    protected function recentActivity(Tenant $tenant, int $limit = 8): Collection
    {
        $paymentEvents = $tenant->paymentHistories()
            ->whereNotNull('paid_at')
            ->orderByDesc('paid_at')
            ->limit($limit)
            ->get()
            ->map(fn (PaymentHistory $payment) => new TenantPortalActivityItem(
                type: 'payment',
                title: 'Rent payment recorded',
                description: $payment->due_date->format('F Y').' · £'.number_format((float) $payment->amount, 2),
                occurredAt: $payment->paid_at,
            ));

        $proofEvents = $tenant->paymentProofs()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (PaymentProof $proof) => new TenantPortalActivityItem(
                type: 'proof',
                title: match ($proof->status) {
                    PaymentProofStatus::Approved => 'Payment proof approved',
                    PaymentProofStatus::Rejected => 'Payment proof not accepted',
                    default => 'Payment proof submitted',
                },
                description: $proof->original_filename,
                occurredAt: $proof->created_at,
            ));

        return $paymentEvents
            ->concat($proofEvents)
            ->sortByDesc(fn (TenantPortalActivityItem $item) => $item->occurredAt)
            ->take($limit)
            ->values();
    }
}
