<?php

namespace App\Services\Payments;

use App\Enums\PaymentProofStatus;
use App\Models\PaymentProof;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaymentProofQueryService
{
    public function pendingCountForLandlord(User $landlord): int
    {
        return PaymentProof::query()
            ->forLandlord($landlord)
            ->pending()
            ->count();
    }

    public function paginateForLandlord(User $landlord, ?string $status = null): LengthAwarePaginator
    {
        return PaymentProof::query()
            ->forLandlord($landlord)
            ->with(['tenant', 'paymentHistory'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @return Collection<int, PaymentProof>
     */
    public function recentForTenant(Tenant $tenant, int $limit = 5): Collection
    {
        return PaymentProof::query()
            ->forTenant($tenant)
            ->with('paymentHistory')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function findForLandlord(User $landlord, int $id): PaymentProof
    {
        return PaymentProof::query()
            ->forLandlord($landlord)
            ->with(['tenant', 'paymentHistory', 'reviewedBy'])
            ->whereKey($id)
            ->firstOrFail();
    }
}
