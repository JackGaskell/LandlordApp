<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\TenantStatus;
use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use BelongsToLandlord, HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone_number',
        'rent_amount',
        'rent_due_day',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rent_amount' => 'decimal:2',
            'rent_due_day' => 'integer',
            'status' => TenantStatus::class,
        ];
    }

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(PaymentHistory::class)->latestOfMany('due_date');
    }

    public function openPayments(): HasMany
    {
        return $this->paymentHistories()->whereIn('status', [
            PaymentStatus::DueSoon,
            PaymentStatus::Overdue,
            PaymentStatus::PartiallyPaid,
        ]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', TenantStatus::Active);
    }
}
