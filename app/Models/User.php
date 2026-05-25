<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'password',
        'stripe_customer_id',
        'stripe_subscription_id',
        'subscription_status',
        'stripe_connect_account_id',
        'stripe_connect_charges_enabled',
        'stripe_connect_details_submitted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_status' => SubscriptionStatus::class,
            'stripe_connect_charges_enabled' => 'boolean',
            'stripe_connect_details_submitted' => 'boolean',
        ];
    }

    public function canAcceptStripeRentPayments(): bool
    {
        return filled($this->stripe_connect_account_id)
            && $this->stripe_connect_charges_enabled;
    }

    /**
     * Full display name (e.g. emails, navigation).
     */
    protected function name(): Attribute
    {
        return Attribute::get(
            fn (): string => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_status instanceof SubscriptionStatus) {
            return $this->subscription_status->grantsAccess();
        }

        return false;
    }

    public function onStripeBilling(): bool
    {
        return filled($this->stripe_customer_id);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function landlordSetting(): HasOne
    {
        return $this->hasOne(LandlordSetting::class);
    }

    public function paymentHistories(): HasManyThrough
    {
        return $this->hasManyThrough(PaymentHistory::class, Tenant::class);
    }
}
