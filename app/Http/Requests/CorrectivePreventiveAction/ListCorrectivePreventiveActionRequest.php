<?php

namespace App\Http\Requests\CorrectivePreventiveAction;

use App\Enums\CorrectivePreventiveAction\Priority;
use App\Enums\CorrectivePreventiveAction\SourceType;
use App\Enums\CorrectivePreventiveAction\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListCorrectivePreventiveActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::enum(Status::class)],
            'source_type' => ['sometimes', 'string', Rule::enum(SourceType::class)],
            'priority' => ['sometimes', 'string', Rule::enum(Priority::class)],
            'from' => ['sometimes', 'date', 'before_or_equal:today'],
            'to' => ['sometimes', 'date', 'before_or_equal:today', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
