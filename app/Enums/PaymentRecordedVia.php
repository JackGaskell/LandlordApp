<?php

namespace App\Enums;

/**
 * How a payment_histories row was created or settled.
 */
enum PaymentRecordedVia: string
{
    case Manual = 'manual';
    case Stripe = 'stripe';
}
