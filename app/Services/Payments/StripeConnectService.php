<?php

namespace App\Services\Payments;

use App\Exceptions\StripeNotConfiguredException;
use App\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Stripe Connect Express — rent is charged on the landlord's connected account (direct charge).
 * The platform does not hold tenant rent payments.
 */
class StripeConnectService
{
    public function isEnabled(): bool
    {
        return config('landlord.stripe.enabled')
            && config('landlord.stripe.connect.enabled')
            && filled(config('services.stripe.secret'))
            && class_exists(StripeClient::class);
    }

    public function canAcceptRentPayments(User $landlord): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        if ($this->connectRequired()) {
            return $landlord->canAcceptStripeRentPayments();
        }

        return true;
    }

    public function connectAccountIdForRent(User $landlord): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        if (! $this->connectRequired()) {
            return null;
        }

        if (! $landlord->canAcceptStripeRentPayments()) {
            return null;
        }

        return $landlord->stripe_connect_account_id;
    }

    public function requireConnectAccountIdForRent(User $landlord): string
    {
        $accountId = $this->connectAccountIdForRent($landlord);

        if (! $accountId) {
            throw \App\Exceptions\StripeLandlordNotReadyException::forRentCheckout();
        }

        return $accountId;
    }

    public function ensureExpressAccount(User $landlord): string
    {
        $client = $this->client();

        if (filled($landlord->stripe_connect_account_id)) {
            return $landlord->stripe_connect_account_id;
        }

        $account = $client->accounts->create([
            'type' => 'express',
            'country' => (string) config('landlord.stripe.connect.country', 'GB'),
            'email' => $landlord->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'metadata' => [
                'landlord_user_id' => (string) $landlord->id,
            ],
        ]);

        $landlord->update([
            'stripe_connect_account_id' => $account->id,
        ]);

        return $account->id;
    }

    public function createOnboardingUrl(User $landlord): string
    {
        $accountId = $this->ensureExpressAccount($landlord);

        $link = $this->client()->accountLinks->create([
            'account' => $accountId,
            'refresh_url' => route('settings.stripe.refresh'),
            'return_url' => route('settings.stripe.return'),
            'type' => 'account_onboarding',
        ]);

        return $link->url;
    }

    public function syncLandlordAccount(User $landlord): User
    {
        if (! filled($landlord->stripe_connect_account_id)) {
            return $landlord;
        }

        try {
            $account = $this->client()->accounts->retrieve($landlord->stripe_connect_account_id);
        } catch (ApiErrorException) {
            return $landlord;
        }

        $landlord->update([
            'stripe_connect_charges_enabled' => (bool) $account->charges_enabled,
            'stripe_connect_details_submitted' => (bool) $account->details_submitted,
        ]);

        return $landlord->fresh();
    }

    protected function connectRequired(): bool
    {
        return (bool) config('landlord.stripe.connect.required', true);
    }

    protected function client(): StripeClient
    {
        if (! config('landlord.stripe.enabled') || ! filled(config('services.stripe.secret'))) {
            throw StripeNotConfiguredException::forFeature('Stripe Connect');
        }

        return new StripeClient((string) config('services.stripe.secret'));
    }
}
