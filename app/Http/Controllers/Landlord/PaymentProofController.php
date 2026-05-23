<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\PaymentProof;
use App\Services\Payments\PaymentProofQueryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentProofController extends Controller
{
    public function __construct(
        protected PaymentProofQueryService $queries,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');

        return view('landlord.payment-proofs.index', [
            'proofs' => $this->queries->paginateForLandlord($request->user(), $status),
            'pendingCount' => $this->queries->pendingCountForLandlord($request->user()),
            'filter' => $status,
        ]);
    }

    public function show(PaymentProof $paymentProof): View
    {
        $this->authorize('view', $paymentProof);

        $paymentProof->load(['tenant', 'paymentHistory', 'reviewedBy']);

        return view('landlord.payment-proofs.show', [
            'proof' => $paymentProof,
        ]);
    }
}
