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

        return view('portal.dashboard', [
            'snapshot' => $snapshot,
            'tenant' => $tenant,
        ]);
    }
}
