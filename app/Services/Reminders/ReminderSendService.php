<?php

namespace App\Services\Reminders;

use App\Enums\ReminderDeliveryStatus;
use App\Models\PaymentHistory;
use App\Models\RentReminderDelivery;
use Throwable;

class ReminderSendService
{
    public function __construct(
        protected ReminderChannelRegistry $channels,
        protected RentReminderDeliveryService $deliveries,
        protected ReminderEligibilityService $eligibility,
    ) {}

    public function send(RentReminderDelivery $delivery): void
    {
        $delivery->loadMissing(['paymentHistory.tenant.landlord.landlordSetting']);

        if ($delivery->status !== ReminderDeliveryStatus::Pending) {
            return;
        }

        $payment = $delivery->paymentHistory;

        if (! $payment) {
            $this->deliveries->markSkipped($delivery, 'Payment record no longer exists.');

            return;
        }

        $settings = $payment->tenant->landlord->landlordSetting ?? null;

        if (! $this->eligibility->landlordRemindersEnabled($settings)) {
            $this->deliveries->markSkipped($delivery, 'Landlord has email reminders disabled.');

            return;
        }

        if ($payment->status->isSettled()) {
            $this->deliveries->markSkipped($delivery, 'Payment is already settled.');

            return;
        }

        if (! $this->eligibility->paymentQualifiesForType($payment, $delivery->reminder_type)) {
            $this->deliveries->markSkipped($delivery, 'Payment no longer matches this reminder type.');

            return;
        }

        try {
            $this->channels
                ->sender($delivery->channel)
                ->send($delivery, $payment);

            $this->deliveries->markSent($delivery);
        } catch (Throwable $exception) {
            if ($this->isPermanentFailure($exception)) {
                $this->deliveries->markSkipped($delivery, $exception->getMessage());

                return;
            }

            throw $exception;
        }
    }

    protected function isPermanentFailure(Throwable $exception): bool
    {
        return str_contains($exception->getMessage(), 'no email address');
    }
}
