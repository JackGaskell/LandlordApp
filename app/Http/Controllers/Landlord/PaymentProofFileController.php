<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\PaymentProof;
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
        $this->authorize('download', $paymentProof);

        return $this->storage->downloadResponse($paymentProof);
    }
}
