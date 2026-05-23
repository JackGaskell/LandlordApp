<?php

namespace App\Services\Reminders\Channels;

use App\Enums\ReminderChannel;
use App\Models\PaymentHistory;
use App\Models\RentReminderDelivery;
use App\Models\Tenant;
use App\Notifications\RentDueReminderNotification;
use App\Services\Reminders\Contracts\ReminderChannelSender;

class EmailReminderChannel implements ReminderChannelSender
{
    public function channel(): ReminderChannel
    {
        return ReminderChannel::Email;
    }

    public function send(RentReminderDelivery $delivery, PaymentHistory $payment): void
    {
        $tenant = $payment->tenant;

        if (! $tenant instanceof Tenant) {
            throw new \RuntimeException('Payment is missing a tenant.');
        }

        if (! $tenant->routeNotificationFor('mail')) {
            throw new \RuntimeException('Tenant has no email address for reminders.');
        }

        $tenant->notify(
            new RentDueReminderNotification(
                $payment,
                $delivery->reminder_type,
                $delivery->signedDayOffset(),
            ),
        );
    }
}
