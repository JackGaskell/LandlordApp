<?php

namespace App\Services\Reminders;

use App\Enums\ReminderChannel;
use App\Enums\ReminderDeliveryStatus;
use App\Enums\ReminderType;
use App\Models\PaymentHistory;
use App\Models\RentReminderDelivery;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

class RentReminderDeliveryService
{
    /**
     * Record a pending delivery or return null when this reminder was already queued today.
     */
    public function recordPending(
        PaymentHistory $payment,
        ReminderType $type,
        int $daysOffset,
        ReminderChannel $channel,
        ?Carbon $dispatchDate = null,
    ): ?RentReminderDelivery {
        $dispatchDate ??= now()->startOfDay();

        try {
            return RentReminderDelivery::query()->create([
                'payment_history_id' => $payment->id,
                'tenant_id' => $payment->tenant_id,
                'landlord_user_id' => $payment->tenant->user_id,
                'reminder_type' => $type,
                'days_offset' => $daysOffset,
                'channel' => $channel,
                'dispatch_date' => $dispatchDate,
                'status' => ReminderDeliveryStatus::Pending,
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateKey($exception)) {
                return null;
            }

            throw $exception;
        }
    }

    public function markSent(RentReminderDelivery $delivery): void
    {
        $delivery->update([
            'status' => ReminderDeliveryStatus::Sent,
            'sent_at' => now(),
            'failure_reason' => null,
        ]);
    }

    public function markFailed(RentReminderDelivery $delivery, string $reason): void
    {
        $delivery->update([
            'status' => ReminderDeliveryStatus::Failed,
            'failure_reason' => $reason,
        ]);
    }

    public function markSkipped(RentReminderDelivery $delivery, string $reason): void
    {
        $delivery->update([
            'status' => ReminderDeliveryStatus::Skipped,
            'failure_reason' => $reason,
        ]);
    }

    protected function isDuplicateKey(QueryException $exception): bool
    {
        $code = $exception->errorInfo[1] ?? null;

        return in_array($code, [1062, 19, 2067, 1555], true);
    }
}
