<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\EventType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class EventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermissionTo('create articles');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Converts "on" to true, and missing/null to false
            'paid_entry' => $this->boolean('paid_entry'),
            'one_hour_periods' => $this->boolean('one_hour_periods'),
            'allow_external_domains' => $this->boolean('allow_external_domains'),
            'links' => empty($this->links) ? [] : $this->links,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [Rule::requiredIf($this->event_type !== EventType::QR_TAG->value), 'nullable', 'string'],
            'description' => [Rule::requiredIf($this->event_type !== EventType::QR_TAG->value), 'nullable', 'string', 'max:4000000'],
            'event_type' => ['required', Rule::enum(EventType::class)],
            'num_of_seats' => [
                'nullable',
                Rule::requiredIf($this->event_type !== EventType::QR_TAG->value),
                'integer',
                'min:1',
            ],

            'paid_entry' => ['boolean'],
            'entry_fee' => ['nullable', 'required_if:paid_entry,true', 'integer', 'min:5'],

            'one_hour_periods' => ['boolean'],
            'one_hour_periods_number' => ['sometimes', 'required_if:one_hour_periods,true', 'integer', 'min:1'],
            'interval_length' => ['sometimes', 'required_if:one_hour_periods,true', 'integer', 'min:0'],

            'display_starts_at' => ['required', 'date'],
            'event_starts_at' => ['sometimes', 'required', 'date'],
            'event_ends_at' => [
                'sometimes',
                'required_if:one_hour_periods,false',
                'nullable',
                'date',
                'after:event_starts_at',
            ],

            'links' => ['nullable', 'array'],
            'links.*' => ['url', 'max:255'],
            'image' => [
                'nullable',
                File::types(['avif', 'jpeg', 'jpg', 'png', 'webp'])
                    ->max(12288),
            ],
            'allow_external_domains' => ['boolean'],
        ];
    }
}
