<?php

namespace App\Http\Requests\Portal;

use App\Models\PaymentHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StorePaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('tenant')?->hasPortalAccess() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = (int) config('landlord.payment_proofs.max_kb', 5120);
        $mimes = config('landlord.payment_proofs.allowed_mimes', ['pdf', 'jpg', 'jpeg', 'png']);

        return [
            'payment_id' => ['required', 'integer', 'exists:payment_histories,id'],
            'proof' => ['required', File::types($mimes)->max($maxKb)],
            'note' => ['nullable', 'string', 'max:500'],
            'mark_as_paid' => ['sometimes', 'boolean'],
            'claimed_paid_at' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function payment(): PaymentHistory
    {
        return PaymentHistory::query()
            ->where('tenant_id', $this->user('tenant')->id)
            ->whereKey($this->integer('payment_id'))
            ->firstOrFail();
    }
}
