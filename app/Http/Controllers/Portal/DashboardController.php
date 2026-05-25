<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Portal\TenantPortalDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected TenantPortalDashboardService $dashboard,
    ) {}

    public function __invoke(): View
    {
        $tenant = auth('tenant')->user();
        $snapshot = $this->dashboard->snapshot($tenant);

        $paymentNotice = match (request()->query('payment')) {
            'success' => 'Thank you — your card payment is processing. Your rent record will update shortly.',
            'cancel' => 'Payment cancelled. You can pay anytime from this page.',
            default => null,
        };

        return view('portal.dashboard', [
            'snapshot' => $snapshot,
            'tenant' => $tenant,
            'paymentNotice' => $paymentNotice,
        ]);
    }
}
