<?php

namespace App\Http\Controllers;

use App\Actions\Payments\MarkPaymentPaidAction;
use App\Actions\Payments\RecordPaymentAction;
use App\Http\Requests\Payment\StorePaymentHistoryRequest;
use App\Http\Requests\Payment\UpdatePaymentHistoryRequest;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Payments\PaymentHistoryService;
use Illuminate\Http\RedirectResponse;

class PaymentHistoryController extends Controller
{
    public function __construct(
        protected RecordPaymentAction $recordPayment,
        protected MarkPaymentPaidAction $markPaymentPaid,
        protected PaymentHistoryService $payments,
    ) {}

    public function store(StorePaymentHistoryRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->recordPayment->execute($tenant, $request->validated());

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('status', 'Payment recorded.');
    }

    public function update(UpdatePaymentHistoryRequest $request, PaymentHistory $payment): RedirectResponse
    {
        $this->payments->update($payment, $request->validated());

        return redirect()
            ->route('tenants.show', $payment->tenant_id)
            ->with('status', 'Payment updated.');
    }

    public function markPaid(PaymentHistory $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $this->markPaymentPaid->execute($payment);

        return redirect()
            ->route('tenants.show', $payment->tenant_id)
            ->with('status', 'Payment marked as paid.');
    }

    public function destroy(PaymentHistory $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $tenantId = $payment->tenant_id;
        $this->payments->delete($payment);

        return redirect()
            ->route('tenants.show', $tenantId)
            ->with('status', 'Payment removed.');
    }
}
