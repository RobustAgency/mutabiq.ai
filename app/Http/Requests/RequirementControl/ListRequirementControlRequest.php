<?php

namespace App\Http\Requests\RequirementControl;

use Illuminate\Validation\Rule;
use App\Enums\RequirementControl\Coverage;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RequirementControl\ReviewStatus;

class ListRequirementControlRequest extends FormRequest
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
            'requirement_id' => ['nullable', 'integer', 'exists:requirements,id'],
            'control_id' => ['nullable', 'integer', 'exists:controls,id'],
            'coverage' => ['nullable', 'string', Rule::enum(Coverage::class)],
            'review_status' => ['nullable', 'string', Rule::enum(ReviewStatus::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
