<?php

namespace App\Http\Requests\AiIncident;

use Illuminate\Validation\Rule;
use App\Enums\AiIncident\Domain;
use App\Enums\AiIncident\IncidentType;
use App\Enums\AiIncident\ResponseTeam;
use App\Enums\AiIncident\ExternalParty;
use App\Enums\AiIncident\IncidentStatus;
use App\Enums\AiIncident\ImpactedDataType;
use App\Enums\AiIncident\IncidentSeverity;
use App\Enums\AiIncident\ResidencyAffected;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AiIncident\AffectedBusinessUnit;
use App\Enums\AiIncident\NotificationRequirement;
use App\Enums\AiIncident\PrimaryRegulatoryFramework;

class UpdateAiIncidentRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'summary' => ['sometimes', 'required', 'string'],
            'incident_type' => ['sometimes', 'required', Rule::enum(IncidentType::class)],
            'domain' => ['sometimes', 'required', Rule::enum(Domain::class)],
            'severity' => ['sometimes', 'required', Rule::enum(IncidentSeverity::class)],
            'status' => ['sometimes', 'required', Rule::enum(IncidentStatus::class)],
            'incident_commander' => ['sometimes', 'required', 'string', 'max:255'],
            'response_team' => ['sometimes', 'required', Rule::enum(ResponseTeam::class)],
            'primary_regulatory_framework' => ['sometimes', 'required', Rule::enum(PrimaryRegulatoryFramework::class)],
            'notification_requirement' => ['sometimes', 'required', Rule::enum(NotificationRequirement::class)],
            'data_residency_affected' => ['sometimes', 'nullable', Rule::enum(ResidencyAffected::class)],
            'regulatory_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'estimated_impacted_users' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'estimated_impacted_records' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'data_types_impacted' => ['sometimes', 'required', 'array'],
            'data_types_impacted.*' => [Rule::enum(ImpactedDataType::class)],
            'affected_business_units' => ['sometimes', 'nullable', 'array', 'min:1'],
            'affected_business_units.*' => [Rule::enum(AffectedBusinessUnit::class)],
            'external_parties_involved' => ['sometimes', 'nullable', 'array'],
            'external_parties_involved.*' => [Rule::enum(ExternalParty::class)],
            'business_impact_description' => ['sometimes', 'nullable', 'string'],
            'impacted_systems' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ai_model_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_models,id'],
            'linked_dataset_id' => ['sometimes', 'nullable', 'integer', 'exists:datasets,id'],
            'linked_risk_id' => ['sometimes', 'nullable', 'integer', 'exists:risks,id'],
            'evidence_link' => ['sometimes', 'nullable', 'url'],
        ];
    }
}
