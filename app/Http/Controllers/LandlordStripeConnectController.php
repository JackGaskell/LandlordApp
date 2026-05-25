<?php

namespace App\Http\Controllers;

use App\Exceptions\StripeNotConfiguredException;
use App\Services\Payments\StripeConnectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandlordStripeConnectController extends Controller
{
    public function __construct(
        protected StripeConnectService $connect,
    ) {}

    public function edit(): View
    {
        $landlord = auth()->user();
        $landlord = $this->connect->syncLandlordAccount($landlord);

        return view('settings.payments', [
            'landlord' => $landlord,
            'connectEnabled' => $this->connect->isEnabled(),
        ]);
    }

    public function connect(): RedirectResponse
    {
        try {
            $url = $this->connect->createOnboardingUrl(auth()->user());

            return redirect()->away($url);
        } catch (StripeNotConfiguredException) {
            return redirect()
                ->route('settings.payments')
                ->with('status', 'Stripe is not configured yet. Add your platform keys to enable card payments.');
        }
    }

    public function return(): RedirectResponse
    {
        $landlord = $this->connect->syncLandlordAccount(auth()->user());

        $message = $landlord->canAcceptStripeRentPayments()
            ? 'Stripe is connected. Tenants can pay rent directly to your account.'
            : 'Stripe setup is in progress. Complete any remaining steps in Stripe if prompted.';

        return redirect()
            ->route('settings.payments')
            ->with('status', $message);
    }

    public function refresh(): RedirectResponse
    {
        try {
            $url = $this->connect->createOnboardingUrl(auth()->user());

            return redirect()->away($url);
        } catch (StripeNotConfiguredException) {
            return redirect()->route('settings.payments');
        }
    }
}
