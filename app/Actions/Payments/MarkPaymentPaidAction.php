<?php

namespace App\Actions\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentRecordedVia;
use App\Enums\PaymentStatus;
use App\Services\Payments\PaymentTrackingService;
use App\Enums\PaymentVerificationStatus;
use App\Mail\Transactional\PaymentReceivedMail;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Marks a payment as fully paid and verified.
 */
class MarkPaymentPaidAction
{
    public function __construct(
        protected PaymentTrackingService $paymentTracking,
    ) {}

    public function execute(
        PaymentHistory $payment,
        ?PaymentVerificationStatus $verification = null,
    ): PaymentHistory {
        return DB::transaction(function () use ($payment, $verification) {
            $payment->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'verification_status' => $verification ?? PaymentVerificationStatus::Verified,
                'recorded_via' => $payment->recorded_via ?? PaymentRecordedVia::Manual,
                'payment_method' => $payment->payment_method ?? PaymentMethod::fromRecordedVia(
                    $payment->recorded_via ?? PaymentRecordedVia::Manual,
                ),
            ]);

            $payment = $this->paymentTracking->sync($payment)->load(['tenant.landlord']);

            if ($payment->tenant?->landlord) {
                $landlord = $payment->tenant->landlord;

                Mail::to($landlord)->queue(
                    (new PaymentReceivedMail($payment, $landlord->name, $payment->tenant->name))
                        ->onQueue(config('landlord.queues.mail')),
                );
            }

            return $payment;
        });
    }
}
