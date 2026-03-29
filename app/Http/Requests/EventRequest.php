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
            'display_starts_at' => ['required', 'date'],
            'event_starts_at' => ['required', 'date', 'after:display_starts_at'],
            'event_ends_at' => ['required', 'date', 'after:event_starts_at'],
            'links' => ['nullable', 'array'],
            'links.*' => ['url', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
