<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TenantStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('tenant')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'property_label' => ['nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'rent_amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999.99'],
            'rent_due_day' => ['sometimes', 'required', 'integer', 'min:1', 'max:28'],
            'status' => ['sometimes', 'required', Rule::enum(TenantStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
