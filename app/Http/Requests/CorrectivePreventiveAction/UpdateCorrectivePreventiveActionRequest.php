<?php

namespace App\Http\Requests\CorrectivePreventiveAction;

use App\Enums\CorrectivePreventiveAction\CapaType;
use App\Enums\CorrectivePreventiveAction\OwnerTeam;
use App\Enums\CorrectivePreventiveAction\Priority;
use App\Enums\CorrectivePreventiveAction\SourceType;
use App\Enums\CorrectivePreventiveAction\Status;
use App\Enums\CorrectivePreventiveAction\VerificationResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'source_type' => ['sometimes', Rule::enum(SourceType::class)],
            'source_id' => ['sometimes', 'string', 'max:255'],
            'model_id' => ['sometimes', 'nullable', 'exists:ai_models,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'capa_type' => ['sometimes', Rule::enum(CapaType::class)],
            'priority' => ['sometimes', Rule::enum(Priority::class)],
            'owner_team' => ['sometimes', Rule::enum(OwnerTeam::class)],
            'assignee' => ['sometimes', 'nullable', 'string', 'max:255'],
            'root_cause' => ['sometimes', 'nullable', 'string'],
            'actions' => ['sometimes', 'nullable', 'string'],
            'due_date' => ['sometimes', 'date'],
            'status' => ['sometimes', Rule::enum(Status::class)],
            'verification_result' => ['sometimes', 'nullable', Rule::enum(VerificationResult::class)],
            'evidence_link' => ['sometimes', 'nullable', 'url', 'max:500'],
            'closed_at' => ['sometimes', 'nullable', 'date'],
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
