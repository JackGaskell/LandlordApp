<?php

namespace App\Services\Reminders\Contracts;

use App\Enums\ReminderChannel;
use App\Models\PaymentHistory;
use App\Models\RentReminderDelivery;

interface ReminderChannelSender
{
    public function channel(): ReminderChannel;

    public function send(RentReminderDelivery $delivery, PaymentHistory $payment): void;
}
