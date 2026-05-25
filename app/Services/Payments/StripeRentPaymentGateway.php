<?php

namespace App\Services\Payments;

use App\Contracts\Payments\RentPaymentGateway;
use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Exceptions\StripeNotConfiguredException;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Stripe Checkout for a single rent period (mode=payment).
 */
class StripeRentPaymentGateway implements RentPaymentGateway
{
    public function isConfigured(): bool
    {
        return config('landlord.stripe.enabled')
            && filled(config('services.stripe.secret'))
            && class_exists(StripeClient::class);
    }

    public function createRentCheckout(PaymentHistory $payment, Tenant $tenant): CheckoutSessionResult
    {
        if (! $this->isConfigured()) {
            throw StripeNotConfiguredException::forFeature('rent checkout');
        }

        $client = new StripeClient((string) config('services.stripe.secret'));

        if ($payment->stripe_checkout_session_id) {
            $resumed = $this->resumeOpenSession($client, $payment->stripe_checkout_session_id);

            if ($resumed !== null) {
                return $resumed;
            }
        }

        $currency = strtolower((string) config('landlord.stripe.currency', 'gbp'));

        $session = $client->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $tenant->email,
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $this->amountInMinorUnits($payment, $currency),
                        'product_data' => [
                            'name' => 'Rent – '.$tenant->name,
                            'description' => 'Rent for '.$payment->due_date->format('F Y'),
                        ],
                    ],
                ],
            ],
            'metadata' => [
                'type' => 'rent_payment',
                'payment_history_id' => (string) $payment->id,
                'tenant_id' => (string) $tenant->id,
                'landlord_user_id' => (string) $tenant->user_id,
            ],
            'success_url' => $this->checkoutUrl('rent_success'),
            'cancel_url' => $this->checkoutUrl('rent_cancel'),
        ]);

        return new CheckoutSessionResult(
            sessionId: $session->id,
            url: $session->url,
        );
    }

    protected function resumeOpenSession(StripeClient $client, string $sessionId): ?CheckoutSessionResult
    {
        try {
            /** @var Session $session */
            $session = $client->checkout->sessions->retrieve($sessionId);

            if ($session->status === 'open' && filled($session->url)) {
                return new CheckoutSessionResult(
                    sessionId: $session->id,
                    url: $session->url,
                );
            }
        } catch (ApiErrorException) {
            // Fall through and create a new session.
        }

        return null;
    }

    protected function amountInMinorUnits(PaymentHistory $payment, string $currency): int
    {
        $amount = (float) $payment->amount;

        return match ($currency) {
            'jpy' => (int) round($amount),
            default => (int) round($amount * 100),
        };
    }

    protected function checkoutUrl(string $key): string
    {
        $path = (string) config("landlord.stripe.checkout_urls.{$key}", '/portal');

        return url($path);
    }
}
