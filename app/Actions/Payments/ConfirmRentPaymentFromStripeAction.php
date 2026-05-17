<?php

namespace App\Actions\Payments;

use App\Enums\PaymentRecordedVia;
use App\Enums\PaymentVerificationStatus;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\DB;

/**
 * Idempotent settlement when Stripe confirms a rent Checkout session.
 */
class ConfirmRentPaymentFromStripeAction
{
    public function __construct(
        protected MarkPaymentPaidAction $markPaymentPaid,
    ) {}

    public function execute(
        string $checkoutSessionId,
        ?string $paymentIntentId = null,
        ?int $paymentHistoryId = null,
    ): ?PaymentHistory {
        $payment = $this->resolvePayment($checkoutSessionId, $paymentHistoryId);

        if (! $payment) {
            return null;
        }

        if ($payment->status->isSettled()) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $checkoutSessionId, $paymentIntentId) {
            $payment->update([
                'stripe_checkout_session_id' => $checkoutSessionId,
                'stripe_payment_intent_id' => $paymentIntentId,
                'recorded_via' => PaymentRecordedVia::Stripe,
            ]);

            return $this->markPaymentPaid->execute(
                $payment->fresh(),
                PaymentVerificationStatus::Verified,
            );
        });
    }

    protected function resolvePayment(string $checkoutSessionId, ?int $paymentHistoryId): ?PaymentHistory
    {
        if ($paymentHistoryId) {
            $byId = PaymentHistory::query()->find($paymentHistoryId);

            if ($byId) {
                return $byId;
            }
        }

        return PaymentHistory::query()
            ->where('stripe_checkout_session_id', $checkoutSessionId)
            ->first();
    }
}
