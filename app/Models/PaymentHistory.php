<?php

namespace App\Models;

use App\Enums\PaymentRecordedVia;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentHistory extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'amount',
        'due_date',
        'paid_at',
        'status',
        'verification_status',
        'recorded_via',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => PaymentStatus::class,
            'verification_status' => PaymentVerificationStatus::class,
            'recorded_via' => PaymentRecordedVia::class,
        ];
    }

    public function wasRecordedViaStripe(): bool
    {
        return $this->recorded_via === PaymentRecordedVia::Stripe;
    }

    public function hasStripeCheckout(): bool
    {
        return filled($this->stripe_checkout_session_id);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Restrict payments to a landlord using a semi-join on tenant ids.
     *
     * Prefer this over whereHas(): the planner can use tenants.user_id,
     * then probe payment_histories by tenant_id without a correlated EXISTS.
     */
    public function scopeForLandlord(Builder $query, User|int $landlord): Builder
    {
        $landlordId = $landlord instanceof User ? $landlord->id : $landlord;

        return $query->whereIn('tenant_id', function ($subquery) use ($landlordId) {
            $subquery->select('id')
                ->from('tenants')
                ->where('user_id', $landlordId);
        });
    }

    public function scopeDueInMonth(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('due_date', [$start, $end]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Overdue);
    }

    public function scopeDueSoon(Builder $query, int $withinDays = 7): Builder
    {
        return $query
            ->where('status', PaymentStatus::DueSoon)
            ->whereBetween('due_date', [
                now()->toDateString(),
                now()->addDays($withinDays)->toDateString(),
            ]);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Paid);
    }

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PaymentStatus::DueSoon,
            PaymentStatus::Overdue,
            PaymentStatus::PartiallyPaid,
        ]);
    }
}
