<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\PaymentHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = $this->route('tenant');

        return $tenant && $this->user()?->can('create', [PaymentHistory::class, $tenant]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'due_date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::enum(PaymentStatus::class)],
            'verification_status' => ['required', Rule::enum(PaymentVerificationStatus::class)],
        ];
    }
}
