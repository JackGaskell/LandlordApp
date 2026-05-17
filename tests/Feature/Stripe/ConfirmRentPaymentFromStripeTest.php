<?php

namespace Tests\Feature\Stripe;

use App\Actions\Payments\ConfirmRentPaymentFromStripeAction;
use App\Enums\PaymentRecordedVia;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmRentPaymentFromStripeTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_checkout_marks_payment_paid_and_verified(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create();
        $payment = PaymentHistory::factory()->for($tenant)->overdue()->create([
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);

        $result = app(ConfirmRentPaymentFromStripeAction::class)->execute(
            checkoutSessionId: 'cs_test_123',
            paymentIntentId: 'pi_test_456',
            paymentHistoryId: $payment->id,
        );

        $this->assertNotNull($result);
        $result->refresh();

        $this->assertSame(PaymentStatus::Paid, $result->status);
        $this->assertSame(PaymentVerificationStatus::Verified, $result->verification_status);
        $this->assertSame(PaymentRecordedVia::Stripe, $result->recorded_via);
        $this->assertSame('pi_test_456', $result->stripe_payment_intent_id);
        $this->assertNotNull($result->paid_at);
    }

    public function test_stripe_confirmation_is_idempotent(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create();
        $payment = PaymentHistory::factory()->for($tenant)->paid()->create([
            'stripe_checkout_session_id' => 'cs_test_dup',
            'recorded_via' => PaymentRecordedVia::Stripe,
        ]);

        $result = app(ConfirmRentPaymentFromStripeAction::class)->execute(
            checkoutSessionId: 'cs_test_dup',
            paymentHistoryId: $payment->id,
        );

        $this->assertSame($payment->id, $result?->id);
        $this->assertSame(1, PaymentHistory::query()->where('stripe_checkout_session_id', 'cs_test_dup')->count());
    }
}
