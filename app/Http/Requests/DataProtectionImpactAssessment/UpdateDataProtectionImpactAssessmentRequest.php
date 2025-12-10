<?php

namespace App\Http\Requests\DataProtectionImpactAssessment;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DataProtectionImpactAssessment\Stage;
use App\Enums\DataProtectionImpactAssessment\Status;
use App\Enums\DataProtectionImpactAssessment\RiskLevel;
use App\Enums\DataProtectionImpactAssessment\Jurisdiction;
use App\Enums\DataProtectionImpactAssessment\FinalDecision;
use App\Enums\DataProtectionImpactAssessment\LinkedAssetsType;
use App\Enums\DataProtectionImpactAssessment\ResidualRiskLevel;

class UpdateDataProtectionImpactAssessmentRequest extends FormRequest
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
            'dpia_name' => ['sometimes', 'string', 'max:255'],
            'ropa_id' => ['sometimes', 'integer', 'exists:record_of_processing_activities,id'],
            'linked_ai_model_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_models,id'],
            'linked_asset_type' => ['sometimes', Rule::enum(LinkedAssetsType::class)],
            'automated_trigger' => ['sometimes', 'boolean'],
            'trigger_reason' => ['sometimes', 'string', 'max:255'],
            'risk_level' => ['sometimes', Rule::enum(RiskLevel::class)],
            'risk_score' => ['sometimes', 'integer', 'min:1', 'max:25'],
            'stage' => ['sometimes', Rule::enum(Stage::class)],
            'completion_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'necessity_justification' => [
                'sometimes',
                Rule::requiredIf($this->input('stage') === Stage::NECESSITY->value),
                'string',
            ],
            'proportionality_assessment' => ['sometimes', 'string'],
            'alternatives_considered' => ['sometimes', 'string'],
            'identified_risks' => [
                'sometimes',
                Rule::requiredIf($this->input('stage') === Stage::RISK_IDENTIFICATION->value),
                'string',
            ],
            'likelihood_assessment' => ['sometimes', 'string'],
            'impact_assessment' => ['sometimes', 'string'],
            'mitigation_measures' => [
                'sometimes',
                Rule::requiredIf($this->input('stage') === Stage::MITIGATION->value),
                'string',
            ],
            'residual_risk_level' => [
                'sometimes',
                Rule::requiredIf(in_array($this->input('stage'), [Stage::DPO_CONSULTATION->value, Stage::APPROVAL->value, Stage::COMPLETED->value])),
                Rule::enum(ResidualRiskLevel::class),
            ],
            'dpo_consulted' => ['sometimes', 'nullable', 'boolean'],
            'dpo_consultation_date' => [
                'sometimes',
                Rule::requiredIf($this->input('dpo_consulted') === true),
                'date',
            ],
            'dpo_advice' => ['sometimes', Rule::requiredIf($this->input('dpo_consulted') === true), 'string'],
            'dpo_user_id' => ['sometimes', Rule::requiredIf($this->input('dpo_consulted') === true), 'integer', 'exists:users,id'],
            'stakeholders_consulted' => ['sometimes', 'nullable', 'array', 'min:1'],
            'stakeholders_consulted.*' => ['integer', 'exists:stakeholders,id'],
            'stakeholder_feedback' => ['sometimes', 'nullable', 'string'],
            'data_subjects_consulted' => ['sometimes', 'boolean'],
            'consultation_method' => ['sometimes', Rule::requiredIf($this->input('dpo_consulted') === true), 'string'],
            'final_decision' => [
                'sometimes',
                Rule::requiredIf($this->input('stage') === Stage::APPROVAL->value),
                Rule::enum(FinalDecision::class),
            ],
            'approval_date' => ['sometimes', Rule::requiredIf($this->input('stage') === Stage::APPROVAL->value), 'date'],
            'approved_by' => ['sometimes', Rule::requiredIf($this->input('stage') === Stage::APPROVAL->value), 'integer', 'exists:users,id'],
            'conditions' => ['sometimes', Rule::requiredIf($this->input('final_decision') === FinalDecision::APPROVED_WITH_CONDITIONS->value), 'string'],
            'status' => ['sometimes', Rule::enum(Status::class)],
            'review_frequency_months' => ['sometimes', 'integer', 'min:1'],
            'applicable_jurisdictions' => ['sometimes', 'array', 'min:1'],
            'applicable_jurisdictions.*' => ['string', Rule::enum(Jurisdiction::class)],
        ];
    }
}
