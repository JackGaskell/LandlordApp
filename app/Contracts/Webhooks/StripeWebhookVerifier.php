<?php

namespace App\Contracts\Webhooks;

use Illuminate\Http\Request;

interface StripeWebhookVerifier
{
    /**
     * @throws \App\Exceptions\StripeNotConfiguredException
     */
    public function verify(Request $request): void;
}
