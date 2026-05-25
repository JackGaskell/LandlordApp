<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'property_label' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'rent_amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'rent_due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
