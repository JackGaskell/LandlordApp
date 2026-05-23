<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\ReviewPaymentProofRequest;
use App\Models\PaymentProof;
use App\Services\Payments\PaymentProofWorkflowService;
use Illuminate\Http\RedirectResponse;

class PaymentProofReviewController extends Controller
{
    public function __construct(
        protected PaymentProofWorkflowService $workflow,
    ) {}

    public function approve(ReviewPaymentProofRequest $request, PaymentProof $paymentProof): RedirectResponse
    {
        $this->authorize('review', $paymentProof);

        $this->workflow->approve(
            $paymentProof,
            $request->user(),
            $request->string('landlord_note')->toString() ?: null,
        );

        return redirect()
            ->route('payment-proofs.show', $paymentProof)
            ->with('status', 'Payment proof approved and rent marked as verified.');
    }

    public function reject(ReviewPaymentProofRequest $request, PaymentProof $paymentProof): RedirectResponse
    {
        $this->authorize('review', $paymentProof);

        $this->workflow->reject(
            $paymentProof,
            $request->user(),
            $request->string('landlord_note')->toString() ?: null,
        );

        return redirect()
            ->route('payment-proofs.show', $paymentProof)
            ->with('status', 'Payment proof rejected. The tenant has been notified to resubmit if needed.');
    }
}
