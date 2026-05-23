<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentOutcome;
use App\Enums\PaymentRecordedVia;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'payment_method',
        'notes',
        'days_late',
        'payment_outcome',
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
            'payment_method' => PaymentMethod::class,
            'payment_outcome' => PaymentOutcome::class,
            'days_late' => 'integer',
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

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function wasPaidOnTime(): bool
    {
        if ($this->payment_outcome === PaymentOutcome::OnTime) {
            return true;
        }

        if ($this->payment_outcome !== null) {
            return false;
        }

        if ($this->status === PaymentStatus::Paid && $this->paid_at) {
            return $this->paid_at->lte($this->due_date->copy()->endOfDay());
        }

        return false;
    }

    public function isCurrentOpenPeriod(): bool
    {
        return in_array($this->status, [PaymentStatus::DueSoon, PaymentStatus::PartiallyPaid], true)
            && $this->due_date->gte(now()->startOfDay()->subDays(3));
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
