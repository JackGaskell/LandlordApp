<?php

namespace App\Policies;

use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;

class PaymentHistoryPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $tenant->user_id === $user->id;
    }

    public function view(User $user, PaymentHistory $paymentHistory): bool
    {
        return $this->ownsPayment($user, $paymentHistory);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $tenant->user_id === $user->id;
    }

    public function update(User $user, PaymentHistory $paymentHistory): bool
    {
        return $this->ownsPayment($user, $paymentHistory);
    }

    public function delete(User $user, PaymentHistory $paymentHistory): bool
    {
        return $this->ownsPayment($user, $paymentHistory);
    }

    protected function ownsPayment(User $user, PaymentHistory $paymentHistory): bool
    {
        return $paymentHistory->tenant()->where('user_id', $user->id)->exists();
    }
}
