<?php

namespace App\Http\Requests\AiRiskRegister;

use Illuminate\Validation\Rule;
use App\Enums\AiRiskRegister\RiskLevel;
use App\Enums\AiRiskRegister\RiskStatus;
use App\Enums\AiRiskRegister\RiskCategory;
use App\Enums\AiRiskRegister\RiskDecision;
use App\Enums\AiRiskRegister\ReviewCadence;
use Illuminate\Foundation\Http\FormRequest;

class StoreAiRiskRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'risk_methodology_id' => ['required', 'exists:risk_methodologies,id'],
            'title' => ['required', 'string', 'max:255'],
            'risk_category' => ['required', Rule::in(array_map(fn ($c) => $c->value, RiskCategory::cases()))],
            'ai_model_id' => ['required', 'exists:ai_models,id'],
            'ai_model_version_id' => ['nullable', 'exists:ai_model_versions,id'],
            'use_case_id' => ['nullable', 'exists:use_cases,id'],
            'description' => ['required', 'string'],
            'related_controls' => ['nullable', 'array'],
            'likelihood_code' => ['required', 'string', 'max:255'],
            'impact_code' => ['required', 'string', 'max:255'],
            'inherent_score' => ['nullable', 'string', 'max:255'],
            'residual_score' => ['nullable', 'string', 'max:255'],
            'risk_level' => ['required', Rule::in(array_map(fn ($c) => $c->value, RiskLevel::cases()))],
            'decision' => ['required', Rule::in(array_map(fn ($c) => $c->value, RiskDecision::cases()))],
            'risk_owner' => ['required', 'exists:stakeholders,id'],
            'review_cadence' => ['required', Rule::in(array_map(fn ($c) => $c->value, ReviewCadence::cases()))],
            'next_review_due' => ['required', 'date'],
            'status' => ['required', Rule::in(array_map(fn ($c) => $c->value, RiskStatus::cases()))],
            'linked_assessment_id' => ['nullable', 'integer'],
            'linked_incident_id' => ['nullable', 'exists:ai_incidents,id'],
            'linked_capa_id' => ['nullable', 'exists:corrective_preventive_actions,id'],
            'evidence_link' => ['nullable', 'string', 'max:500'],
            'likelihood_label_snapshot' => ['nullable', 'string', 'max:255'],
            'impact_label_snapshot' => ['nullable', 'string', 'max:255'],
            'method_name_snapshot' => ['nullable', 'string', 'max:255'],
            'created_by' => ['required', 'email'],
        ];
    }
}
