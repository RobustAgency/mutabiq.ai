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

class StoreDataProtectionImpactAssessmentRequest extends FormRequest
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
            'dpia_name' => ['required', 'string', 'max:255'],
            'ropo_id' => ['required', 'integer', 'exists:record_of_processing_activities,id'],
            'linked_ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
            'linked_asset_type' => ['required', Rule::enum(LinkedAssetsType::class)],
            'automated_trigger' => ['required', 'boolean'],
            'trigger_reason' => ['required', 'string', 'max:255'],
            'risk_level' => ['required', Rule::enum(RiskLevel::class)],
            'risk_score' => ['required', 'integer', 'min:1', 'max:25'],
            'stage' => ['required', Rule::enum(Stage::class)],
            'completion_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'necessity_justification' => [
                Rule::requiredIf($this->input('stage') === Stage::NECESSITY->value),
                'string',
            ],
            'proportionality_assessment' => ['required', 'string'],
            'alternatives_considered' => ['required', 'string'],
            'identified_risks' => [
                Rule::requiredIf($this->input('stage') === Stage::RISK_IDENTIFICATION->value),
                'string',
            ],
            'likelihood_assessment' => ['required', 'string'],
            'impact_assessment' => ['required', 'string'],
            'mitigation_measures' => [
                Rule::requiredIf($this->input('stage') === Stage::MITIGATION->value),
                'string',
            ],
            'residual_risk_level' => [
                Rule::requiredIf(in_array($this->input('stage'), [Stage::DPO_CONSULTATION->value, Stage::APPROVAL->value, Stage::COMPLETED->value])),
                Rule::enum(ResidualRiskLevel::class),
            ],
            'dpo_consulted' => ['nullable', 'boolean'],
            'dpo_consultation_date' => [
                Rule::requiredIf($this->input('dpo_consulted') === true),
                'date',
            ],
            'dpo_advice' => [Rule::requiredIf($this->input('dpo_consulted') === true), 'string'],
            'dpo_user_id' => [Rule::requiredIf($this->input('dpo_consulted') === true), 'integer', 'exists:users,id'],
            'stakeholders_consulted' => ['nullable', 'array', 'min:1'],
            'stakeholders_consulted.*' => ['integer', 'exists:stakeholders,id'],
            'stakeholder_feedback' => ['nullable', 'string'],
            'data_subjects_consulted' => ['required', 'boolean'],
            'consultation_method' => [Rule::requiredIf($this->input('dpo_consulted') === true), 'string'],
            'final_decision' => [
                Rule::requiredIf($this->input('stage') === Stage::APPROVAL->value),
                Rule::enum(FinalDecision::class),
            ],
            'approval_date' => [Rule::requiredIf($this->input('stage') === Stage::APPROVAL->value), 'date'],
            'approved_by' => [Rule::requiredIf($this->input('stage') === Stage::APPROVAL->value), 'integer', 'exists:users,id'],
            'conditions' => [Rule::requiredIf($this->input('final_decision') === FinalDecision::APPROVED_WITH_CONDITIONS->value), 'string'],
            'status' => ['required', Rule::enum(Status::class)],
            'review_frequency_months' => ['required', 'integer', 'min:1'],
            'applicable_jurisdictions' => ['required', 'array', 'min:1'],
            'applicable_jurisdictions.*' => ['string', Rule::enum(Jurisdiction::class)],
        ];
    }
}
