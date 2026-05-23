<?php

namespace App\Jobs\Reminders;

use App\Models\RentReminderDelivery;
use App\Services\Reminders\RentReminderDeliveryService;
use App\Services\Reminders\ReminderSendService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends one rent reminder for a tracked delivery record.
 */
class SendRentReminderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $rentReminderDeliveryId,
    ) {
        $this->onQueue(config('landlord.queues.reminders'));
    }

    public function handle(ReminderSendService $sender): void
    {
        $delivery = RentReminderDelivery::query()->find($this->rentReminderDeliveryId);

        if (! $delivery) {
            return;
        }

        $sender->send($delivery);

        Log::info('Rent reminder processed', [
            'delivery_id' => $delivery->id,
            'payment_id' => $delivery->payment_history_id,
            'tenant_id' => $delivery->tenant_id,
            'reminder_type' => $delivery->reminder_type->value,
            'channel' => $delivery->channel->value,
            'status' => $delivery->status->value,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $delivery = RentReminderDelivery::query()->find($this->rentReminderDeliveryId);

        if ($delivery) {
            app(RentReminderDeliveryService::class)->markFailed(
                $delivery,
                $exception?->getMessage() ?? 'Unknown error',
            );
        }

        Log::error('Rent reminder job failed', [
            'delivery_id' => $this->rentReminderDeliveryId,
            'message' => $exception?->getMessage(),
        ]);
    }
}
