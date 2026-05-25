<?php

namespace App\Services\Payments;

use App\Enums\PaymentProofStatus;
use App\Enums\PaymentVerificationStatus;
use App\Events\PaymentProofs\PaymentProofApproved;
use App\Events\PaymentProofs\PaymentProofRejected;
use App\Events\PaymentProofs\PaymentProofSubmitted;
use App\Models\PaymentHistory;
use App\Models\PaymentProof;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Rent\RentPeriodAutomationService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentProofWorkflowService
{
    public function __construct(
        protected PaymentProofStorageService $storage,
        protected PaymentTrackingService $paymentTracking,
        protected RentPeriodAutomationService $rentPeriods,
    ) {}

    public function submit(
        Tenant $tenant,
        UploadedFile $file,
        ?PaymentHistory $payment = null,
        ?string $tenantNote = null,
        bool $markAsPaid = false,
        ?Carbon $claimedPaidAt = null,
    ): PaymentProof {
        if ($payment) {
            $this->assertTenantOwnsPayment($tenant, $payment);
            $this->assertNoPendingProofForPayment($payment);
        }

        return DB::transaction(function () use ($tenant, $file, $payment, $tenantNote, $markAsPaid, $claimedPaidAt) {
            $stored = $this->storage->store($tenant, $file);

            $proof = PaymentProof::query()->create([
                'tenant_id' => $tenant->id,
                'payment_history_id' => $payment?->id,
                'status' => PaymentProofStatus::Pending,
                ...$stored,
                'tenant_note' => $tenantNote,
                'tenant_marked_paid' => $markAsPaid,
                'claimed_paid_at' => $markAsPaid ? ($claimedPaidAt ?? now()) : null,
            ]);

            if ($payment && $markAsPaid) {
                $payment->update([
                    'paid_at' => $claimedPaidAt ?? now(),
                    'verification_status' => PaymentVerificationStatus::Pending,
                ]);
                $this->paymentTracking->sync($payment);
            } elseif ($payment) {
                $payment->update([
                    'verification_status' => PaymentVerificationStatus::Pending,
                ]);
            }

            PaymentProofSubmitted::dispatch($proof->fresh(['tenant.landlord', 'paymentHistory']));

            return $proof;
        });
    }

    public function approve(PaymentProof $proof, User $landlord, ?string $landlordNote = null): PaymentProof
    {
        $this->assertLandlordOwnsProof($landlord, $proof);
        $this->assertProofIsPending($proof);

        return DB::transaction(function () use ($proof, $landlord, $landlordNote) {
            $proof->update([
                'status' => PaymentProofStatus::Approved,
                'landlord_note' => $landlordNote,
                'reviewed_by_user_id' => $landlord->id,
                'reviewed_at' => now(),
            ]);

            if ($payment = $proof->paymentHistory) {
                $payment->update([
                    'paid_at' => $proof->claimed_paid_at ?? $payment->paid_at ?? now(),
                    'verification_status' => PaymentVerificationStatus::Verified,
                ]);
                $payment = $this->paymentTracking->sync($payment);
                $this->rentPeriods->advanceAfterPeriodSettled($payment);
            }

            $proof = $proof->fresh(['tenant', 'paymentHistory']);

            PaymentProofApproved::dispatch($proof);

            return $proof;
        });
    }

    public function reject(PaymentProof $proof, User $landlord, ?string $landlordNote = null): PaymentProof
    {
        $this->assertLandlordOwnsProof($landlord, $proof);
        $this->assertProofIsPending($proof);

        return DB::transaction(function () use ($proof, $landlord, $landlordNote) {
            $proof->update([
                'status' => PaymentProofStatus::Rejected,
                'landlord_note' => $landlordNote,
                'reviewed_by_user_id' => $landlord->id,
                'reviewed_at' => now(),
            ]);

            if ($payment = $proof->paymentHistory) {
                if ($proof->tenant_marked_paid) {
                    $payment->update([
                        'paid_at' => null,
                        'verification_status' => PaymentVerificationStatus::Disputed,
                    ]);
                    $this->paymentTracking->sync($payment);
                } else {
                    $payment->update([
                        'verification_status' => PaymentVerificationStatus::Unverified,
                    ]);
                }
            }

            $proof = $proof->fresh(['tenant', 'paymentHistory']);

            PaymentProofRejected::dispatch($proof);

            return $proof;
        });
    }

    protected function assertTenantOwnsPayment(Tenant $tenant, PaymentHistory $payment): void
    {
        if ($payment->tenant_id !== $tenant->id) {
            abort(403);
        }
    }

    protected function assertLandlordOwnsProof(User $landlord, PaymentProof $proof): void
    {
        if ($proof->tenant->user_id !== $landlord->id) {
            abort(403);
        }
    }

    protected function assertProofIsPending(PaymentProof $proof): void
    {
        if (! $proof->isPending()) {
            throw ValidationException::withMessages([
                'proof' => 'This submission has already been reviewed.',
            ]);
        }
    }

    protected function assertNoPendingProofForPayment(PaymentHistory $payment): void
    {
        $exists = PaymentProof::query()
            ->where('payment_history_id', $payment->id)
            ->where('status', PaymentProofStatus::Pending)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'proof' => 'You already have a payment confirmation awaiting review for this rent period.',
            ]);
        }
    }
}
