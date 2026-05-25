<?php

namespace App\Services\Portal;

use App\DataTransferObjects\Portal\TenantCollectionSummary;
use App\DataTransferObjects\Portal\TenantPaymentHistoryItem;
use App\DataTransferObjects\Portal\TenantPaymentStatusSummary;
use App\DataTransferObjects\Portal\TenantPaymentSummaryCard;
use App\DataTransferObjects\Portal\TenantUpcomingRent;
use App\Enums\PaymentStatus;
use App\Enums\TenantCollectionStatus;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Payments\PaymentTrackingService;
use App\Services\Reliability\LatePaymentDetector;
use App\Services\Rent\RentScheduleService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Derives tenant-facing payment lifecycle state from real payment records.
 */
class TenantPaymentLifecycleService
{
    public function __construct(
        protected PaymentTrackingService $paymentTracking,
        protected LatePaymentDetector $latePayments,
        protected RentScheduleService $rentSchedule,
    ) {}

    /**
     * Sync open periods (status, outcome) and refresh tenant reliability for the portal.
     */
    public function refreshOpenPaymentStatuses(Tenant $tenant): void
    {
        $this->paymentTracking->syncOutstandingPaymentsForTenant($tenant);
    }

    public function currentPeriod(Tenant $tenant): ?PaymentHistory
    {
        return $tenant->paymentHistories()
            ->outstanding()
            ->orderBy('due_date')
            ->first();
    }

    public function latestPeriod(Tenant $tenant): ?PaymentHistory
    {
        return $tenant->paymentHistories()
            ->orderByDesc('due_date')
            ->first();
    }

    public function resolveFocusPayment(Tenant $tenant): ?PaymentHistory
    {
        return $this->currentPeriod($tenant) ?? $this->latestPeriod($tenant);
    }

    public function daysUntilDue(CarbonInterface $dueDate, ?CarbonInterface $asOf = null): int
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();
        $due = $dueDate->copy()->startOfDay();

        return (int) $asOf->diffInDays($due, false);
    }

    public function daysOverdue(PaymentHistory $payment, ?CarbonInterface $asOf = null): int
    {
        if (! $this->latePayments->isPastDueUnpaid($payment, $asOf)) {
            return 0;
        }

        $asOf = ($asOf ?? now())->copy()->startOfDay();

        return (int) $payment->due_date->copy()->startOfDay()->diffInDays($asOf);
    }

    public function collectionStatus(?PaymentHistory $payment): TenantCollectionStatus
    {
        if (! $payment) {
            return TenantCollectionStatus::Clear;
        }

        return match ($payment->status) {
            PaymentStatus::Paid => TenantCollectionStatus::OnTrack,
            PaymentStatus::DueSoon => TenantCollectionStatus::Upcoming,
            PaymentStatus::Overdue => TenantCollectionStatus::ActionNeeded,
            PaymentStatus::PartiallyPaid => TenantCollectionStatus::PartialProgress,
        };
    }

    public function buildCollectionSummary(Tenant $tenant, ?PaymentHistory $payment): TenantCollectionSummary
    {
        $status = $this->collectionStatus($payment);
        $dueDate = $payment?->due_date ?? $this->rentSchedule->nextDueDate($tenant);
        $amount = $payment ? (float) $payment->amount : $this->rentSchedule->scheduledAmount($tenant);
        $daysUntil = $this->daysUntilDue($dueDate);
        $daysOverdue = $payment ? $this->daysOverdue($payment) : 0;
        $isOverdue = $payment?->status === PaymentStatus::Overdue;

        return new TenantCollectionSummary(
            status: $status,
            headline: $status->headline(),
            message: $this->collectionMessage($status, $payment, $daysUntil, $daysOverdue),
            tone: $status->tone(),
            paymentStatus: $payment?->status,
            amount: $amount,
            dueDate: $dueDate,
            daysUntilDue: max(0, $daysUntil),
            daysOverdue: $daysOverdue > 0 ? $daysOverdue : null,
            isOverdue: $isOverdue,
            paymentId: $payment?->id,
        );
    }

    public function buildUpcomingRent(Tenant $tenant, ?PaymentHistory $payment): TenantUpcomingRent
    {
        $dueDate = $payment?->due_date ?? $this->rentSchedule->nextDueDate($tenant);
        $amount = $payment ? (float) $payment->amount : $this->rentSchedule->scheduledAmount($tenant);
        $daysUntil = $this->daysUntilDue($dueDate);
        $daysOverdue = $payment ? $this->daysOverdue($payment) : 0;
        $isOverdue = $payment?->status === PaymentStatus::Overdue;

        return new TenantUpcomingRent(
            amount: $amount,
            dueDate: $dueDate,
            daysUntilDue: max(0, $daysUntil),
            daysOverdue: $daysOverdue > 0 ? $daysOverdue : null,
            isOverdue: $isOverdue,
            dueLabel: $this->dueLabel($dueDate, $daysUntil, $daysOverdue, $isOverdue),
            paymentId: $payment?->id,
            collectionStatus: $this->collectionStatus($payment),
        );
    }

    public function buildPaymentStatus(?PaymentHistory $payment): TenantPaymentStatusSummary
    {
        if (! $payment) {
            return new TenantPaymentStatusSummary(
                status: PaymentStatus::DueSoon,
                headline: 'Nothing due right now',
                message: 'When your next rent period is ready, it will show up here.',
                canUploadProof: false,
                canPayOnline: false,
            );
        }

        $status = $payment->status;
        $canPay = $status->isOutstanding() && ! config('landlord.portal.pay_online_coming_soon', true);

        return new TenantPaymentStatusSummary(
            status: $status,
            headline: $this->statusHeadline($status),
            message: $this->statusMessage($status, $payment),
            canUploadProof: $status->isOutstanding(),
            canPayOnline: $canPay,
        );
    }

    /**
     * @return Collection<int, TenantPaymentHistoryItem>
     */
    public function buildPaymentHistory(Tenant $tenant, int $limit = 12): Collection
    {
        $focusId = $this->resolveFocusPayment($tenant)?->id;

        return $tenant->paymentHistories()
            ->orderByDesc('due_date')
            ->limit($limit)
            ->get()
            ->map(fn (PaymentHistory $payment) => new TenantPaymentHistoryItem(
                id: $payment->id,
                dueDate: $payment->due_date,
                amount: (float) $payment->amount,
                status: $payment->status,
                paidAt: $payment->paid_at,
                periodLabel: $payment->due_date->format('F Y'),
                subtitle: $this->historySubtitle($payment),
                isCurrentPeriod: $payment->id === $focusId,
            ));
    }

    /**
     * @return list<TenantPaymentSummaryCard>
     */
    public function buildSummaryCards(
        Tenant $tenant,
        TenantCollectionSummary $collection,
        int $onTimeStreak,
        float $consistencyPercent,
    ): array {
        $paidCount = $tenant->paymentHistories()->paid()->count();

        return [
            new TenantPaymentSummaryCard(
                label: $collection->isOverdue ? 'Outstanding' : 'Next rent',
                value: $collection->amountFormatted() ?? '—',
                hint: $collection->isOverdue
                    ? ($collection->daysOverdue
                        ? $collection->daysOverdue.' '.str('day')->plural($collection->daysOverdue).' past due'
                        : 'Past due date')
                    : ($collection->dueDateFormatted() ? 'Due '.$collection->dueDateFormatted() : null),
                tone: $collection->isOverdue ? 'warning' : 'brand',
            ),
            new TenantPaymentSummaryCard(
                label: 'Collection status',
                value: $collection->status->label(),
                hint: $collection->paymentStatus?->label(),
                tone: $collection->tone,
            ),
            new TenantPaymentSummaryCard(
                label: 'On-time streak',
                value: (string) $onTimeStreak,
                hint: $onTimeStreak === 1 ? 'month' : 'months in a row',
                tone: $onTimeStreak > 0 ? 'success' : 'neutral',
            ),
            new TenantPaymentSummaryCard(
                label: 'Consistency',
                value: number_format($consistencyPercent, 0).'%',
                hint: $paidCount.' paid '.str('period')->plural($paidCount).' on record',
                tone: $consistencyPercent >= 80 ? 'success' : 'neutral',
            ),
        ];
    }

    protected function collectionMessage(
        TenantCollectionStatus $status,
        ?PaymentHistory $payment,
        int $daysUntil,
        int $daysOverdue,
    ): string {
        return match ($status) {
            TenantCollectionStatus::OnTrack => 'Your latest rent is recorded — keep the rhythm going.',
            TenantCollectionStatus::Upcoming => $daysUntil === 0
                ? 'Rent is due today. Paying on time is the easiest way to protect your score.'
                : ($daysUntil === 1
                    ? 'Rent is due tomorrow — a good moment to get ahead of the date.'
                    : "You have {$daysUntil} days until rent is due. A little planning goes a long way."),
            TenantCollectionStatus::ActionNeeded => $daysOverdue > 0
                ? "This payment is {$daysOverdue} ".str('day')->plural($daysOverdue).' past the due date. If you have paid, upload your receipt so your record stays accurate.'
                : 'This payment is past the due date. If you have paid, uploading your receipt helps us update your record.',
            TenantCollectionStatus::PartialProgress => 'Part of this month is recorded. Finish the rest when you are ready, or upload your receipt if you have paid.',
            TenantCollectionStatus::Clear => 'Nothing due at the moment. Your next rent period will appear here when it is ready.',
        };
    }

    protected function dueLabel(
        CarbonInterface $dueDate,
        int $daysUntil,
        int $daysOverdue,
        bool $isOverdue,
    ): string {
        if ($isOverdue) {
            return 'Due date passed — confirm when paid';
        }

        if ($dueDate->isToday()) {
            return 'Due today';
        }

        if ($daysUntil === 1) {
            return 'Due tomorrow';
        }

        if ($daysUntil >= 2 && $daysUntil <= 7) {
            return 'Due '.$dueDate->format('l');
        }

        if ($daysUntil >= 8 && $daysUntil <= 21) {
            return $daysUntil.' '.str('day')->plural($daysUntil).' remaining';
        }

        if ($daysUntil > 21) {
            return 'Payment due soon';
        }

        return 'Due '.$dueDate->format('j M Y');
    }

    protected function statusHeadline(PaymentStatus $status): string
    {
        return match ($status) {
            PaymentStatus::Paid => 'All done for this month',
            PaymentStatus::DueSoon => 'Coming up',
            PaymentStatus::Overdue => 'Let\'s get this sorted',
            PaymentStatus::PartiallyPaid => 'Almost there',
        };
    }

    protected function statusMessage(PaymentStatus $status, PaymentHistory $payment): string
    {
        return match ($status) {
            PaymentStatus::Paid => $payment->paid_at
                ? 'Recorded on '.$payment->paid_at->format('j M Y').' — thanks for staying consistent.'
                : 'This month is marked as paid.',
            PaymentStatus::DueSoon => 'Your rent is coming up. Paying on or before the due date keeps your score and streak healthy.',
            PaymentStatus::Overdue => 'If you have already paid, confirm payment below so your record can be updated.',
            PaymentStatus::PartiallyPaid => 'Part of this month is on record. Upload your receipt or finish the rest when you are ready.',
        };
    }

    protected function historySubtitle(PaymentHistory $payment): string
    {
        if ($payment->paid_at) {
            $late = $payment->days_late;

            if ($late === null || $late === 0) {
                return 'Paid '.$payment->paid_at->format('j M Y').' · on time';
            }

            return 'Paid '.$payment->paid_at->format('j M Y').' · '.$late.' '.str('day')->plural($late).' after due date';
        }

        if ($payment->status === PaymentStatus::Overdue) {
            $days = $this->daysOverdue($payment);

            return $days > 0
                ? 'Due '.$payment->due_date->format('j M Y').' · '.$days.' '.str('day')->plural($days).' past due date'
                : 'Due '.$payment->due_date->format('j M Y').' · needs confirming';
        }

        return 'Due '.$payment->due_date->format('j M Y');
    }
}
