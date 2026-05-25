<?php

namespace App\Services\Dashboard;

use App\Enums\PaymentStatus;
use App\Enums\TenantStatus;
use App\Models\PaymentHistory;
use App\Models\PaymentProof;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Payments\PaymentStatusService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use stdClass;

class CollectionHealthService
{
    private const TENANT_LIST_LIMIT = 15;

    private const ACTIVITY_LIMIT = 8;

    public function __construct(
        protected PaymentStatusService $paymentStatus,
    ) {}

    /**
     * @return array{
     *     total_monthly_rent: string,
     *     paid_amount: string,
     *     due_soon_count: int,
     *     due_soon_amount: string,
     *     overdue_count: int,
     *     overdue_amount: string,
     *     paid_this_month_count: int,
     *     collected_this_month: string,
     *     expected_this_month: string,
     *     collection_rate: float,
     *     overdue_tenants: Collection<int, Tenant>,
     *     due_soon_tenants: Collection<int, Tenant>,
     *     paid_tenants: Collection<int, Tenant>,
     *     recent_activity: Collection<int, PaymentHistory>,
     * }
     */
    public function snapshot(User $landlord): array
    {
        $landlordId = $landlord->id;
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $dueSoonDays = $this->paymentStatus->dueSoonDays();

        $monthly = $this->monthlyCollectionStats($landlordId, $startOfMonth, $endOfMonth);
        $overdue = $this->aggregateByStatus($landlordId, fn (Builder $q) => $q->overdue());
        $dueSoon = $this->aggregateDueSoon($landlordId, $dueSoonDays);

        $expected = (float) $monthly->expected_total;
        $collected = (float) $monthly->collected_total;
        $collectionRate = $expected > 0
            ? round(($collected / $expected) * 100, 1)
            : 100.0;

        return [
            'total_monthly_rent' => number_format($this->activeMonthlyRentTotal($landlordId), 2, '.', ''),
            'paid_amount' => number_format($collected, 2, '.', ''),
            'due_soon_count' => (int) $dueSoon->tenant_count,
            'due_soon_amount' => number_format((float) $dueSoon->amount_total, 2, '.', ''),
            'overdue_count' => (int) $overdue->tenant_count,
            'overdue_amount' => number_format((float) $overdue->amount_total, 2, '.', ''),
            'paid_this_month_count' => (int) $monthly->paid_tenant_count,
            'collected_this_month' => number_format($collected, 2, '.', ''),
            'expected_this_month' => number_format($expected, 2, '.', ''),
            'collection_rate' => $collectionRate,
            'overdue_tenants' => $this->tenantsWithOverduePayments($landlordId),
            'due_soon_tenants' => $this->tenantsWithDueSoonPayments($landlordId, $dueSoonDays),
            'paid_tenants' => $this->tenantsPaidThisMonth($landlordId, $startOfMonth, $endOfMonth),
            'recent_activity' => $this->recentPaymentActivity($landlordId),
            'pending_confirmation_count' => $this->pendingConfirmationCount($landlordId),
            'pending_confirmations' => $this->pendingConfirmations($landlordId),
        ];
    }

    protected function pendingConfirmationCount(int $landlordId): int
    {
        return PaymentProof::query()
            ->forLandlord($landlordId)
            ->pending()
            ->count();
    }

    /**
     * @return Collection<int, PaymentProof>
     */
    protected function pendingConfirmations(int $landlordId): Collection
    {
        return PaymentProof::query()
            ->forLandlord($landlordId)
            ->pending()
            ->with(['tenant:id,name', 'paymentHistory:id,due_date,amount'])
            ->latest()
            ->limit(self::TENANT_LIST_LIMIT)
            ->get();
    }

    /**
     * Single aggregate query for the month banner and “Paid” stat card.
     * Avoids loading every payment row into PHP to sum amounts.
     */
    protected function monthlyCollectionStats(int $landlordId, string $start, string $end): stdClass
    {
        $paid = PaymentStatus::Paid->value;

        return PaymentHistory::query()
            ->forLandlord($landlordId)
            ->dueInMonth($start, $end)
            ->selectRaw(
                'COALESCE(SUM(amount), 0) as expected_total,
                COALESCE(SUM(CASE WHEN status = ? THEN amount ELSE 0 END), 0) as collected_total,
                COUNT(DISTINCT CASE WHEN status = ? THEN tenant_id END) as paid_tenant_count',
                [$paid, $paid],
            )
            ->toBase()
            ->first();
    }

    /**
     * Distinct tenant count + sum of amounts for a status bucket (overdue).
     */
    protected function aggregateByStatus(int $landlordId, callable $scope): stdClass
    {
        $query = PaymentHistory::query()->forLandlord($landlordId);
        $scope($query);

        return $query
            ->selectRaw('COUNT(DISTINCT tenant_id) as tenant_count, COALESCE(SUM(amount), 0) as amount_total')
            ->toBase()
            ->first();
    }

    protected function aggregateDueSoon(int $landlordId, int $withinDays): stdClass
    {
        return PaymentHistory::query()
            ->forLandlord($landlordId)
            ->dueSoon($withinDays)
            ->selectRaw('COUNT(DISTINCT tenant_id) as tenant_count, COALESCE(SUM(amount), 0) as amount_total')
            ->toBase()
            ->first();
    }

    /**
     * One indexed SUM on tenants instead of summing payments.
     */
    protected function activeMonthlyRentTotal(int $landlordId): float
    {
        return (float) Tenant::query()
            ->forLandlord($landlordId)
            ->where('status', TenantStatus::Active)
            ->sum('rent_amount');
    }

    /**
     * Tenant rows only (not every open payment). withMin orders by earliest overdue due date.
     */
    protected function tenantsWithOverduePayments(int $landlordId): Collection
    {
        return Tenant::query()
            ->forLandlord($landlordId)
            ->active()
            ->whereHas('paymentHistories', fn (Builder $q) => $q->overdue())
            ->withMin(['paymentHistories as next_due_date' => fn (Builder $q) => $q->overdue()], 'due_date')
            ->orderBy('next_due_date')
            ->limit(self::TENANT_LIST_LIMIT)
            ->get(['id', 'name', 'rent_amount', 'rent_due_day']);
    }

    protected function tenantsWithDueSoonPayments(int $landlordId, int $withinDays): Collection
    {
        return Tenant::query()
            ->forLandlord($landlordId)
            ->active()
            ->whereHas(
                'paymentHistories',
                fn (Builder $q) => $q->dueSoon($withinDays),
            )
            ->withMin(
                ['paymentHistories as next_due_date' => fn (Builder $q) => $q->dueSoon($withinDays)],
                'due_date',
            )
            ->orderBy('next_due_date')
            ->limit(self::TENANT_LIST_LIMIT)
            ->get(['id', 'name', 'rent_amount', 'rent_due_day']);
    }

    /**
     * Tenants with at least one paid period due this month, ordered by amount collected.
     */
    protected function tenantsPaidThisMonth(int $landlordId, string $start, string $end): Collection
    {
        return Tenant::query()
            ->forLandlord($landlordId)
            ->active()
            ->whereHas(
                'paymentHistories',
                fn (Builder $q) => $q->paid()->dueInMonth($start, $end),
            )
            ->withSum(
                [
                    'paymentHistories as collected_this_month' => fn (Builder $q) => $q
                        ->paid()
                        ->dueInMonth($start, $end),
                ],
                'amount',
            )
            ->orderByDesc('collected_this_month')
            ->limit(self::TENANT_LIST_LIMIT)
            ->get(['id', 'name', 'rent_amount', 'rent_due_day']);
    }

    /**
     * Bounded activity feed with eager-loaded tenant (2 queries total).
     */
    protected function recentPaymentActivity(int $landlordId): Collection
    {
        return PaymentHistory::query()
            ->forLandlord($landlordId)
            ->with(['tenant:id,name'])
            ->select(['id', 'tenant_id', 'amount', 'due_date', 'status', 'updated_at'])
            ->orderByDesc('updated_at')
            ->limit(self::ACTIVITY_LIMIT)
            ->get();
    }
}
