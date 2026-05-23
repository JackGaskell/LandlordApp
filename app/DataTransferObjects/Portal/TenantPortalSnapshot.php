<?php

namespace App\DataTransferObjects\Portal;

use App\DataTransferObjects\Reliability\TenantReliabilityProfile;
use App\Models\PaymentHistory;
use Illuminate\Support\Collection;

readonly class TenantPortalSnapshot
{
    /**
     * @param  Collection<int, TenantPaymentHistoryItem>  $paymentHistory
     * @param  Collection<int, TenantPortalActivityItem>  $recentActivity
     * @param  list<TenantPaymentSummaryCard>  $summaryCards
     */
    public function __construct(
        public ?PaymentHistory $currentPayment,
        public TenantCollectionSummary $collection,
        public TenantUpcomingRent $upcomingRent,
        public TenantPaymentStatusSummary $paymentStatus,
        public TenantReliabilityProfile $reliability,
        public Collection $paymentHistory,
        public Collection $recentActivity,
        public array $summaryCards,
        public bool $payOnlineComingSoon,
    ) {}
}
