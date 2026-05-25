<?php

namespace Tests\Unit\Payments;

use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Payments\StripeRentPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeRentPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_not_configured_without_stripe_enabled(): void
    {
        config([
            'landlord.stripe.enabled' => false,
            'services.stripe.secret' => 'sk_test_123',
        ]);

        $gateway = app(StripeRentPaymentGateway::class);

        $this->assertFalse($gateway->isConfigured());
    }

    public function test_rent_checkout_success_url_defaults_to_portal(): void
    {
        $this->assertSame(
            '/portal?payment=success',
            config('landlord.stripe.checkout_urls.rent_success'),
        );

        $this->assertSame(
            '/portal?payment=cancel',
            config('landlord.stripe.checkout_urls.rent_cancel'),
        );
    }
}
