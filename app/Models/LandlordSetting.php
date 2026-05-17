<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordSetting extends Model
{
    /** @use HasFactory<\Database\Factories\LandlordSettingFactory> */
    use BelongsToLandlord, HasFactory;

    protected $fillable = [
        'user_id',
        'reminder_days_before',
        'overdue_reminder_days',
        'email_reminders_enabled',
    ];

    protected function casts(): array
    {
        return [
            'reminder_days_before' => 'array',
            'overdue_reminder_days' => 'array',
            'email_reminders_enabled' => 'boolean',
        ];
    }
}
