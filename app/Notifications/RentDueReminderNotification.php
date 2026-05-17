<?php

namespace App\Notifications;

use App\Enums\ReminderType;
use App\Mail\Reminders\RentDueReminderMail;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Email sent to a tenant about an upcoming or overdue rent payment.
 * Dispatched from SendRentReminderJob (already queued).
 */
class RentDueReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PaymentHistory $payment,
        public ReminderType $reminderType,
        public int $daysOffset,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Tenant $notifiable): RentDueReminderMail
    {
        return (new RentDueReminderMail(
            payment: $this->payment,
            reminderType: $this->reminderType,
            daysOffset: $this->daysOffset,
            tenant: $notifiable,
        ))->to($notifiable->routeNotificationFor('mail'));
    }
}
