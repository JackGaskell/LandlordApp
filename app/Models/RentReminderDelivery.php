<?php

namespace App\Models;

use App\Enums\ReminderChannel;
use App\Enums\ReminderDeliveryStatus;
use App\Enums\ReminderType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentReminderDelivery extends Model
{
    /** @use HasFactory<\Database\Factories\RentReminderDeliveryFactory> */
    use HasFactory;

    protected $fillable = [
        'payment_history_id',
        'tenant_id',
        'landlord_user_id',
        'reminder_type',
        'days_offset',
        'channel',
        'dispatch_date',
        'status',
        'sent_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'reminder_type' => ReminderType::class,
            'channel' => ReminderChannel::class,
            'dispatch_date' => 'date',
            'status' => ReminderDeliveryStatus::class,
            'sent_at' => 'datetime',
            'days_offset' => 'integer',
        ];
    }

    public function paymentHistory(): BelongsTo
    {
        return $this->belongsTo(PaymentHistory::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_user_id');
    }

    public function signedDayOffset(): int
    {
        return $this->reminder_type->signedOffset($this->days_offset);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ReminderDeliveryStatus::Pending);
    }
}
