<?php

namespace App\Events\PaymentProofs;

use App\Models\PaymentProof;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProofRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PaymentProof $proof,
    ) {}
}
