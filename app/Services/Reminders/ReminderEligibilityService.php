<?php

namespace App\Services\Reminders;

use App\Enums\PaymentStatus;
use App\Enums\ReminderType;
use App\Models\LandlordSetting;
use App\Models\PaymentHistory;

class ReminderEligibilityService
{
    public function landlordRemindersEnabled(?LandlordSetting $settings): bool
    {
        return (bool) $settings?->email_reminders_enabled;
    }

    public function paymentQualifiesForType(PaymentHistory $payment, ReminderType $type): bool
    {
        if ($payment->status->isSettled()) {
            return false;
        }

        return match ($type) {
            ReminderType::BeforeDue => $payment->status === PaymentStatus::DueSoon,
            ReminderType::AfterDue => in_array($payment->status, [
                PaymentStatus::Overdue,
                PaymentStatus::PartiallyPaid,
            ], true),
        };
    }

    public function shouldSendOnDate(PaymentHistory $payment, int $signedOffset, ?\Carbon\CarbonInterface $date = null): bool
    {
        $date ??= now();

        return $payment->due_date->copy()->addDays($signedOffset)->isSameDay($date);
    }
}
