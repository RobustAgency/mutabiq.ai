<?php

namespace App\Http\Requests\IncidentAction;

use Illuminate\Validation\Rule;
use App\Enums\IncidentAction\ActionType;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\IncidentAction\ExecutionStatus;
use App\Enums\IncidentAction\ApprovalRequired;
use App\Enums\IncidentAction\ValidationResult;

class UpdateIncidentActionRequest extends FormRequest
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
            'ai_incident_id' => ['sometimes', 'required', 'integer', 'exists:ai_incidents,id'],
            'action_type' => ['sometimes', 'required', Rule::enum(ActionType::class)],
            'execution_status' => ['sometimes', 'required', Rule::enum(ExecutionStatus::class)],
            'description' => ['sometimes', 'required', 'string'],
            'performed_by' => ['sometimes', 'required', 'integer', 'exists:stakeholders,id'],
            'individual_name' => ['nullable', 'string', 'max:255'],
            'depends_on' => ['nullable', 'string', 'max:255'],
            'approval_required' => ['nullable', Rule::enum(ApprovalRequired::class)],
            'estimated_duration' => ['nullable', 'string'],
            'actual_duration' => ['nullable', 'string'],
            'started_at' => ['sometimes', 'required', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'validation_result' => ['sometimes', 'required', Rule::enum(ValidationResult::class)],
            'validation_notes' => ['nullable', 'string'],
            'linked_release_id' => ['nullable', 'string'],
            'evidence_link' => ['nullable', 'url', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'completed_at.after_or_equal' => 'The completed at must be after or equal to started at.',
        ];
    }
}
