<?php

namespace App\Http\Requests\RequirementControl;

use Illuminate\Validation\Rule;
use App\Enums\RequirementControl\Coverage;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RequirementControl\ReviewStatus;

class UpdateRequirementControlRequest extends FormRequest
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
            'requirement_id' => ['sometimes', 'required', 'integer', 'exists:requirements,id'],
            'control_id' => ['sometimes', 'required', 'integer', 'exists:controls,id'],
            'ai_model_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_models,id'],
            'coverage' => ['sometimes', 'required', 'string', Rule::enum(Coverage::class)],
            'interpretation_notes' => ['sometimes', 'required', 'string'],
            'residual_gaps' => ['sometimes', 'required', 'string'],
            'review_status' => ['sometimes', 'nullable', 'string', Rule::enum(ReviewStatus::class)],
            'reviewed_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'reviewed_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
