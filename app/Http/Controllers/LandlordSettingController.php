<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\UpdateLandlordSettingRequest;
use App\Models\LandlordSetting;
use App\Services\Settings\LandlordSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandlordSettingController extends Controller
{
    public function __construct(
        protected LandlordSettingService $settings,
    ) {}

    public function edit(): View
    {
        $setting = $this->settings->forLandlord(auth()->user());

        $this->authorize('view', $setting);

        return view('settings.edit', compact('setting'));
    }

    public function update(UpdateLandlordSettingRequest $request, LandlordSetting $setting): RedirectResponse
    {
        $this->settings->update($setting, $request->validated());

        return redirect()
            ->route('settings.edit')
            ->with('status', 'Reminder settings saved.');
    }
}
