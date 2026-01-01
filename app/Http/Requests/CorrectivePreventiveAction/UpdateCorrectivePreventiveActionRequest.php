<?php

namespace App\Http\Requests\CorrectivePreventiveAction;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CorrectivePreventiveAction\Status;
use App\Enums\CorrectivePreventiveAction\CapaType;
use App\Enums\CorrectivePreventiveAction\Priority;
use App\Enums\CorrectivePreventiveAction\OwnerTeam;
use App\Enums\CorrectivePreventiveAction\SourceType;
use App\Enums\CorrectivePreventiveAction\VerificationResult;

class UpdateCorrectivePreventiveActionRequest extends FormRequest
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
        $rules = [
            'source_type' => ['sometimes', 'required', Rule::enum(SourceType::class)],
            'source_reference' => ['sometimes', 'required', 'string', 'max:255'],
            'ai_model_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_models,id'],
            'dataset_id' => ['sometimes', 'nullable', 'integer', 'exists:datasets,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'capa_type' => ['sometimes', 'required', Rule::enum(CapaType::class)],
            'priority' => ['sometimes', 'required', Rule::enum(Priority::class)],
            'root_cause' => ['sometimes', 'nullable', 'string'],
            'actions' => ['sometimes', 'required', 'string'],
            'owner_team' => ['sometimes', 'required', Rule::enum(OwnerTeam::class)],
            'assignee' => ['sometimes', 'nullable', 'string', 'max:255'],
            'due_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', Rule::enum(Status::class)],
            'success_criteria' => ['sometimes', 'nullable', 'string'],
            'linked_training' => ['sometimes', 'nullable', 'string', 'max:255'],
            'estimated_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'verification_result' => ['sometimes', 'nullable', Rule::enum(VerificationResult::class)],
            'effectiveness_review_date' => ['sometimes', 'nullable', 'date'],
            'evidence_link' => ['sometimes', 'nullable', 'url', 'max:500'],
        ];

        // Require verification_result when status is closed
        if ($this->input('status') === Status::CLOSED->value) {
            $rules['verification_result'] = ['required', Rule::enum(VerificationResult::class)];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'verification_result.required' => 'Verification result is required when status is closed.',
        ];
    }
}
