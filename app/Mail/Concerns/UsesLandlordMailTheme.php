<?php

namespace App\Mail\Concerns;

trait UsesLandlordMailTheme
{
    /**
     * @return array<string, mixed>
     */
    protected function mailTheme(): array
    {
        return [
            'appName' => config('app.name'),
            'brandColor' => config('landlord.mail.brand_color'),
            'supportEmail' => config('landlord.mail.support_address'),
            'appUrl' => config('app.url'),
        ];
    }
}
