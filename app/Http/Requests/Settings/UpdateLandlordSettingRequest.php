<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLandlordSettingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['reminder_days_before', 'overdue_reminder_days'] as $field) {
            if (! $this->has($field)) {
                continue;
            }

            $value = $this->input($field);

            if (is_string($value)) {
                $this->merge([
                    $field => array_values(array_filter(array_map(
                        'intval',
                        array_map('trim', explode(',', $value)),
                    ))),
                ]);
            }

            if (is_array($value)) {
                $this->merge([
                    $field => array_values(array_filter(array_map('intval', $value))),
                ]);
            }
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('setting')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reminder_days_before' => ['required', 'array', 'min:1'],
            'reminder_days_before.*' => ['integer', 'min:0', 'max:60'],
            'overdue_reminder_days' => ['required', 'array', 'min:1'],
            'overdue_reminder_days.*' => ['integer', 'min:1', 'max:60'],
            'email_reminders_enabled' => ['required', 'boolean'],
        ];
    }
}
