<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StorePaymentProofRequest;
use App\Services\Payments\PaymentProofQueryService;
use App\Services\Payments\PaymentProofWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentProofController extends Controller
{
    public function __construct(
        protected PaymentProofWorkflowService $workflow,
        protected PaymentProofQueryService $queries,
    ) {}

    public function index(): View
    {
        $tenant = auth('tenant')->user();

        return view('portal.payment-proofs.index', [
            'proofs' => $this->queries->recentForTenant($tenant, 20),
            'tenant' => $tenant,
        ]);
    }

    public function store(StorePaymentProofRequest $request): RedirectResponse
    {
        $tenant = $request->user('tenant');
        $payment = $request->payment();

        $this->workflow->submit(
            tenant: $tenant,
            file: $request->file('proof'),
            payment: $payment,
            tenantNote: $request->string('note')->toString() ?: null,
            markAsPaid: $request->boolean('mark_as_paid', true),
            claimedPaidAt: $request->filled('claimed_paid_at')
                ? Carbon::parse($request->input('claimed_paid_at'))
                : null,
        );

        return redirect()
            ->route('portal.dashboard')
            ->with('status', 'Thanks — we have your confirmation. We will update your record once it has been reviewed.');
    }
}
