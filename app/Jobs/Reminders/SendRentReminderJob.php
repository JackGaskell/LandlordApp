<?php

namespace App\Jobs\Reminders;

use App\Enums\ReminderType;
use App\Models\PaymentHistory;
use App\Notifications\RentDueReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Sends one rent reminder email for a specific payment period.
 */
class SendRentReminderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $paymentHistoryId,
        public ReminderType $reminderType,
        public int $daysOffset,
    ) {
        $this->onQueue(config('landlord.queues.reminders'));
    }

    public function handle(): void
    {
        $payment = PaymentHistory::query()
            ->with(['tenant.landlord.landlordSetting'])
            ->find($this->paymentHistoryId);

        if (! $payment) {
            return;
        }

        $settings = $payment->tenant->landlord->landlordSetting;

        if ($settings && ! $settings->email_reminders_enabled) {
            return;
        }

        if ($payment->status->isSettled()) {
            return;
        }

        $payment->tenant->notify(
            new RentDueReminderNotification($payment, $this->reminderType, $this->daysOffset),
        );

        Log::info('Rent reminder sent', [
            'payment_id' => $payment->id,
            'tenant_id' => $payment->tenant_id,
            'reminder_type' => $this->reminderType->value,
            'days_offset' => $this->daysOffset,
        ]);
    }
}
