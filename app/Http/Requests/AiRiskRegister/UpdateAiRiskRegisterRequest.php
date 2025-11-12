<?php

namespace App\Http\Requests\AiRiskRegister;

use App\Enums\AiRiskRegister\ReviewCadence;
use App\Enums\AiRiskRegister\RiskCategory;
use App\Enums\AiRiskRegister\RiskDecision;
use App\Enums\AiRiskRegister\RiskLevel;
use App\Enums\AiRiskRegister\RiskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAiRiskRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'risk_category' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, RiskCategory::cases()))],
            'ai_model_id' => ['sometimes', 'exists:ai_models,id'],
            'ai_model_version_id' => ['nullable', 'exists:ai_model_versions,id'],
            'use_case_id' => ['nullable', 'exists:use_cases,id'],
            'description' => ['sometimes', 'string'],
            'related_controls' => ['nullable', 'array'],
            'likelihood_code' => ['sometimes', 'string', 'max:255'],
            'impact_code' => ['sometimes', 'string', 'max:255'],
            'inherent_score' => ['nullable', 'string', 'max:255'],
            'residual_score' => ['nullable', 'string', 'max:255'],
            'risk_level' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, RiskLevel::cases()))],
            'decision' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, RiskDecision::cases()))],
            'risk_owner' => ['sometimes', 'exists:stakeholders,id'],
            'review_cadence' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, ReviewCadence::cases()))],
            'next_review_due' => ['sometimes', 'date'],
            'status' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, RiskStatus::cases()))],
            'linked_assessment_id' => ['nullable', 'integer'],
            'linked_incident_id' => ['nullable', 'exists:ai_incidents,id'],
            'linked_capa_id' => ['nullable', 'exists:corrective_preventive_actions,id'],
            'evidence_link' => ['nullable', 'string', 'max:500'],
            'likelihood_label_snapshot' => ['nullable', 'string', 'max:255'],
            'impact_label_snapshot' => ['nullable', 'string', 'max:255'],
            'method_name_snapshot' => ['nullable', 'string', 'max:255'],
            'created_by' => ['sometimes', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'The risk title must be a string.',
            'ai_model_id.exists' => 'The selected AI model is invalid.',
            'description.string' => 'The risk description must be a string.',
            'method_id.integer' => 'The risk methodology must be an integer.',
            'risk_owner.exists' => 'The selected risk owner is invalid.',
            'next_review_due.date' => 'The next review due must be a valid date.',
        ];
    }
}
