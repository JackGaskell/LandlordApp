<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentStatus;
use App\Enums\PaymentVerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('payment')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999.99'],
            'due_date' => ['sometimes', 'required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'required', Rule::enum(PaymentStatus::class)],
            'verification_status' => ['sometimes', 'required', Rule::enum(PaymentVerificationStatus::class)],
        ];
    }
}
