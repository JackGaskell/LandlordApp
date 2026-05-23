<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PaymentProof;
use App\Services\Payments\PaymentProofStorageService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofFileController extends Controller
{
    public function __construct(
        protected PaymentProofStorageService $storage,
    ) {}

    public function show(PaymentProof $paymentProof): StreamedResponse
    {
        abort_unless($paymentProof->tenant_id === auth('tenant')->id(), 403);

        return $this->storage->downloadResponse($paymentProof);
    }
}
