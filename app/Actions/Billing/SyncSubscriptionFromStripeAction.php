<?php

namespace App\Actions\Billing;

use App\Enums\SubscriptionStatus;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Maps Stripe subscription objects onto the users table.
 */
class SyncSubscriptionFromStripeAction
{
    /**
     * @param  array<string, mixed>  $session  checkout.session object
     */
    public function fromCheckoutSession(array $session): void
    {
        $userId = (int) data_get($session, 'metadata.user_id', 0);
        $customerId = data_get($session, 'customer');
        $subscriptionId = data_get($session, 'subscription');

        if (! $userId) {
            Log::warning('Stripe checkout.session.completed missing metadata.user_id', [
                'session_id' => $session['id'] ?? null,
            ]);

            return;
        }

        $landlord = User::query()->find($userId);

        if (! $landlord) {
            return;
        }

        $landlord->update([
            'stripe_customer_id' => $customerId ? (string) $customerId : $landlord->stripe_customer_id,
            'stripe_subscription_id' => $subscriptionId ? (string) $subscriptionId : $landlord->stripe_subscription_id,
            'subscription_status' => SubscriptionStatus::Active,
        ]);
    }

    /**
     * @param  array<string, mixed>  $subscription  Stripe subscription object
     */
    public function fromSubscriptionObject(array $subscription): void
    {
        $subscriptionId = (string) ($subscription['id'] ?? '');

        if ($subscriptionId === '') {
            return;
        }

        $landlord = User::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->orWhere('stripe_customer_id', (string) ($subscription['customer'] ?? ''))
            ->first();

        if (! $landlord) {
            return;
        }

        $status = $this->mapStatus((string) ($subscription['status'] ?? ''));

        $landlord->update([
            'stripe_customer_id' => (string) ($subscription['customer'] ?? $landlord->stripe_customer_id),
            'stripe_subscription_id' => $subscriptionId,
            'subscription_status' => $status,
        ]);
    }

    /**
     * @param  array<string, mixed>  $invoice
     */
    public function fromInvoiceObject(array $invoice): void
    {
        $customerId = (string) ($invoice['customer'] ?? '');

        if ($customerId === '') {
            return;
        }

        User::query()
            ->where('stripe_customer_id', $customerId)
            ->update(['subscription_status' => SubscriptionStatus::PastDue]);
    }

    protected function mapStatus(string $stripeStatus): ?SubscriptionStatus
    {
        return match ($stripeStatus) {
            'trialing' => SubscriptionStatus::Trialing,
            'active' => SubscriptionStatus::Active,
            'past_due' => SubscriptionStatus::PastDue,
            'canceled', 'unpaid' => SubscriptionStatus::Canceled,
            'incomplete', 'incomplete_expired' => SubscriptionStatus::Incomplete,
            default => null,
        };
    }
}
