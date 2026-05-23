<?php

namespace App\Enums;

enum ReminderChannel: string
{
    case Email = 'email';
    case Sms = 'sms';
    case Push = 'push';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Sms => 'SMS',
            self::Push => 'Push notification',
        };
    }

    public function isEnabled(): bool
    {
        return in_array($this->value, config('landlord.reminders.enabled_channels', ['email']), true);
    }
}
