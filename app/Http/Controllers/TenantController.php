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
        $tenant = $this->createTenant->execute(
            $request->user(),
            $request->validated(),
        );

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('status', 'Tenant created with their first rent period scheduled.');
    }

    public function show(Tenant $tenant): View
    {
        $this->authorize('view', $tenant);

        $tenant->load(['paymentHistories' => fn ($query) => $query->orderByDesc('due_date')]);

        return view('tenants.show', [
            'tenant' => $tenant,
            'reliability' => $this->reliability->score($tenant),
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
