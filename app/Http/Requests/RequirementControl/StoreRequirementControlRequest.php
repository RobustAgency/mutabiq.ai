<?php

namespace App\Http\Requests\RequirementControl;

use Illuminate\Validation\Rule;
use App\Enums\RequirementControl\Coverage;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RequirementControl\ReviewStatus;

class StoreRequirementControlRequest extends FormRequest
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
            'requirement_id' => ['required', 'integer', 'exists:requirements,id'],
            'control_id' => ['required', 'integer', 'exists:controls,id'],
            'ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
            'coverage' => ['required', 'string', Rule::enum(Coverage::class)],
            'interpretation_notes' => ['required', 'string'],
            'residual_gaps' => ['required', 'string'],
            'review_status' => ['nullable', 'string', Rule::enum(ReviewStatus::class)],
            'reviewed_by' => ['nullable', 'integer', 'exists:users,id'],
            'reviewed_at' => ['nullable', 'date'],
        ];
    }
}
