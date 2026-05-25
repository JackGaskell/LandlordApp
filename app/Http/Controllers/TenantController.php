<?php

namespace App\Http\Controllers;

use App\Actions\Tenants\CreateTenantAction;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Models\Tenant;
use App\Services\Rent\RentScheduleService;
use App\Services\Tenants\TenantReliabilityService;
use App\Services\Tenants\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(
        protected TenantService $tenants,
        protected CreateTenantAction $createTenant,
        protected TenantReliabilityService $reliability,
        protected RentScheduleService $rentSchedule,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Tenant::class);

        return view('tenants.index', [
            'tenants' => $this->tenants->paginateForLandlord(auth()->user()),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        return view('tenants.create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $result = $this->createTenant->execute(
            $request->user(),
            $request->validated(),
        );

        $redirect = redirect()
            ->route('tenants.show', $result['tenant'])
            ->with('status', 'Tenant added. Rent reminders and payment tracking run automatically from here.');

        if ($result['portal_invite_url']) {
            $redirect->with('portal_invite_url', $result['portal_invite_url']);
        }

        return $redirect;
    }

    public function show(Tenant $tenant): View
    {
        $this->authorize('view', $tenant);

        $tenant->load(['paymentHistories' => fn ($query) => $query->orderByDesc('due_date')]);

        return view('tenants.show', [
            'tenant' => $tenant,
            'reliability' => $this->reliability->score($tenant),
            'reliabilityProfile' => $this->reliability->profile($tenant),
            'nextDueDate' => $this->rentSchedule->nextDueDate($tenant),
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);

        return view('tenants.edit', compact('tenant'));
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->tenants->update($tenant, $request->validated());

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('status', 'Tenant updated.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->authorize('delete', $tenant);

        $this->tenants->delete($tenant);

        return redirect()
            ->route('tenants.index')
            ->with('status', 'Tenant removed.');
    }
}
