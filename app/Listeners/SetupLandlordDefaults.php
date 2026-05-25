<?php

namespace App\Listeners;

use App\Services\Settings\LandlordSettingService;
use Illuminate\Auth\Events\Registered;

class SetupLandlordDefaults
{
    public function __construct(
        protected LandlordSettingService $settings,
    ) {}

    public function handle(Registered $event): void
    {
        $this->settings->forLandlord($event->user);
    }
}
