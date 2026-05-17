<?php

namespace App\Policies;

use App\Models\LandlordSetting;
use App\Models\User;

class LandlordSettingPolicy
{
    public function view(User $user, LandlordSetting $landlordSetting): bool
    {
        return $landlordSetting->user_id === $user->id;
    }

    public function update(User $user, LandlordSetting $landlordSetting): bool
    {
        return $landlordSetting->user_id === $user->id;
    }
}
