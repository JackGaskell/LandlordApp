<?php

namespace App\Http\Controllers\Portal;

use App\Actions\Payments\CreateRentCheckoutAction;
use App\Exceptions\StripeNotConfiguredException;
use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RentCheckoutController extends Controller
{
    public function __construct(
        protected CreateRentCheckoutAction $createCheckout,
    ) {}

    public function store(Request $request, PaymentHistory $paymentHistory): RedirectResponse
    {
        $tenant = auth('tenant')->user();

        abort_unless($paymentHistory->tenant_id === $tenant->id, 403);

        if (! $paymentHistory->status->isOutstanding()) {
            return redirect()
                ->route('portal.dashboard')
                ->with('status', 'This rent period is already recorded as paid.');
        }

        try {
            $session = $this->createCheckout->execute($paymentHistory);

            return redirect()->away($session->url);
        } catch (StripeNotConfiguredException) {
            return redirect()
                ->route('portal.dashboard')
                ->with('status', 'Online card payments are not available right now. You can still confirm payment with a receipt.');
        }
    }
}
