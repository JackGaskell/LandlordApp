<?php

namespace App\Exceptions;

use RuntimeException;

class StripeLandlordNotReadyException extends RuntimeException
{
    public static function forRentCheckout(): self
    {
        return new self('Your landlord has not finished connecting Stripe to receive card payments.');
    }
}
