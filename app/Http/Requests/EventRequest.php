<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\EventType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'event_type' => ['required', Rule::enum(EventType::class)],
            'num_of_seats' => ['required', 'integer', 'min:1'],
            'paid_entry' => ['boolean'],
            'entry_fee' => ['nullable', 'required_if:paid_entry,true', 'integer', 'min:5'],
            'display_starts_at' => ['required', 'date'],
            'event_starts_at' => ['required', 'date', 'after:display_starts_at'],
            'event_ends_at' => ['required', 'date', 'after:event_starts_at'],
            'links' => ['nullable', 'array'],
            'links.*' => ['url', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
