<?php

namespace Tests\Feature\Portal;

use App\Contracts\Payments\RentPaymentGateway;
use App\DataTransferObjects\Billing\CheckoutSessionResult;
use App\Enums\PaymentStatus;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RentCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'landlord.stripe.enabled' => true,
            'landlord.portal.pay_online_coming_soon' => false,
        ]);
    }

    public function test_tenant_can_start_stripe_checkout_for_outstanding_rent(): void
    {
        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);

        $payment = PaymentHistory::factory()->dueSoon()->for($tenant)->create();

        $this->mock(RentPaymentGateway::class, function ($mock) use ($payment, $tenant) {
            $mock->shouldReceive('createRentCheckout')
                ->once()
                ->with(
                    \Mockery::on(fn ($p) => $p->is($payment)),
                    \Mockery::on(fn ($t) => $t->is($tenant)),
                )
                ->andReturn(new CheckoutSessionResult(
                    sessionId: 'cs_test_checkout',
                    url: 'https://checkout.stripe.com/pay/cs_test_checkout',
                ));
        });

        $this->actingAs($tenant, 'tenant')
            ->post(route('portal.payments.checkout', $payment))
            ->assertRedirect('https://checkout.stripe.com/pay/cs_test_checkout');

        $payment->refresh();

        $this->assertSame('cs_test_checkout', $payment->stripe_checkout_session_id);
    }

    public function test_tenant_cannot_checkout_another_tenants_payment(): void
    {
        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);

        $otherPayment = PaymentHistory::factory()->dueSoon()->create();

        $this->actingAs($tenant, 'tenant')
            ->post(route('portal.payments.checkout', $otherPayment))
            ->assertForbidden();
    }

    public function test_checkout_redirects_when_rent_already_paid(): void
    {
        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);

        $payment = PaymentHistory::factory()->paid()->for($tenant)->create();

        $this->actingAs($tenant, 'tenant')
            ->post(route('portal.payments.checkout', $payment))
            ->assertRedirect(route('portal.dashboard'))
            ->assertSessionHas('status');
    }

    public function test_dashboard_shows_pay_now_when_online_payments_enabled(): void
    {
        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);

        PaymentHistory::factory()->dueSoon()->for($tenant)->create([
            'due_date' => now()->addDays(3),
        ]);

        $this->actingAs($tenant, 'tenant')
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Pay now');
    }

    public function test_dashboard_hides_pay_now_when_coming_soon_flag_set(): void
    {
        config(['landlord.portal.pay_online_coming_soon' => true]);

        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);

        PaymentHistory::factory()->dueSoon()->for($tenant)->create([
            'due_date' => now()->addDays(3),
        ]);

        $this->actingAs($tenant, 'tenant')
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Confirm payment')
            ->assertDontSee('Pay now');
    }
}
