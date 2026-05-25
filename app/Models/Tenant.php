<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\TenantStatus;
use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Tenant extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use Authenticatable, BelongsToLandlord, CanResetPassword, HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'name',
        'property_label',
        'email',
        'password',
        'phone_number',
        'rent_amount',
        'rent_due_day',
        'status',
        'notes',
        'portal_enabled_at',
        'last_login_at',
        'portal_invite_token',
        'portal_invite_expires_at',
        'reliability_score',
        'reliability_current_streak',
        'reliability_best_streak',
        'reliability_on_time_count',
        'reliability_late_count',
        'reliability_missed_count',
        'reliability_consistency_rate',
        'reliability_tracked_periods',
        'reliability_calculated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'portal_invite_token',
    ];

    protected function casts(): array
    {
        return [
            'rent_amount' => 'decimal:2',
            'rent_due_day' => 'integer',
            'status' => TenantStatus::class,
            'password' => 'hashed',
            'portal_enabled_at' => 'datetime',
            'last_login_at' => 'datetime',
            'portal_invite_expires_at' => 'datetime',
            'reliability_score' => 'decimal:2',
            'reliability_consistency_rate' => 'decimal:2',
            'reliability_calculated_at' => 'datetime',
        ];
    }

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
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

    public function scopePortalEnabled(Builder $query): Builder
    {
        return $query->whereNotNull('portal_enabled_at');
    }

    public function hasPortalAccess(): bool
    {
        return $this->portal_enabled_at !== null
            && filled($this->password);
    }

    public function hasPendingInvite(): bool
    {
        return filled($this->portal_invite_token)
            && $this->portal_invite_expires_at?->isFuture();
    }

    public function markLoggedIn(): void
    {
        $this->forceFill(['last_login_at' => now()])->save();
    }

    public function issuePortalInvite(): string
    {
        $token = Str::random(64);

        $this->forceFill([
            'portal_invite_token' => hash('sha256', $token),
            'portal_invite_expires_at' => now()->addDays(config('landlord.portal.invite_expiry_days', 7)),
            'portal_enabled_at' => $this->portal_enabled_at ?? now(),
        ])->save();

        return $token;
    }

    public function clearPortalInvite(): void
    {
        $this->forceFill([
            'portal_invite_token' => null,
            'portal_invite_expires_at' => null,
        ])->save();
    }

    public function matchesInviteToken(string $token): bool
    {
        if (! $this->hasPendingInvite()) {
            return false;
        }

        return hash_equals($this->portal_invite_token, hash('sha256', $token));
    }

    public function firstName(): string
    {
        $parts = preg_split('/\s+/', trim($this->name), 2);

        return $parts[0] ?? $this->name;
    }
}
