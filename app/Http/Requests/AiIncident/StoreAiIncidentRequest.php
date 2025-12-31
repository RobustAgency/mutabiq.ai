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

class StoreAiIncidentRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'incident_type' => ['required', Rule::enum(IncidentType::class)],
            'domain' => ['required', Rule::enum(Domain::class)],
            'severity' => ['required', Rule::enum(IncidentSeverity::class)],
            'status' => ['required', Rule::enum(IncidentStatus::class)],
            'incident_commander' => ['required', 'string', 'max:255'],
            'response_team' => ['required', Rule::enum(ResponseTeam::class)],
            'primary_regulatory_framework' => ['required', Rule::enum(PrimaryRegulatoryFramework::class)],
            'notification_requirement' => ['required', Rule::enum(NotificationRequirement::class)],
            'data_residency_affected' => ['nullable', Rule::enum(ResidencyAffected::class)],
            'regulatory_reference' => ['nullable', 'string', 'max:255'],
            'estimated_impacted_users' => ['nullable', 'integer', 'min:0'],
            'estimated_impacted_records' => ['required', 'integer', 'min:0'],
            'data_types_impacted' => ['required', 'array'],
            'data_types_impacted.*' => [Rule::enum(ImpactedDataType::class)],
            'affected_business_units' => ['nullable', 'array', 'min:1'],
            'affected_business_units.*' => [Rule::enum(AffectedBusinessUnit::class)],
            'external_parties_involved' => ['nullable', 'array'],
            'external_parties_involved.*' => [Rule::enum(ExternalParty::class)],
            'business_impact_description' => ['nullable', 'string'],
            'impacted_systems' => ['nullable', 'string', 'max:255'],
            'ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
            'linked_dataset_id' => ['nullable', 'integer', 'exists:datasets,id'],
            'linked_risk_id' => ['nullable', 'integer', 'exists:risks,id'],
            'evidence_link' => ['nullable', 'url'],
        ];
    }
}
