<?php

namespace App\Http\Requests\AiIncident;

use App\Enums\AiIncident\IncidentCategory;
use App\Enums\AiIncident\IncidentSeverity;
use App\Enums\AiIncident\IncidentStage;
use App\Enums\AiIncident\IncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'category' => ['sometimes', 'required', 'string', Rule::enum(IncidentCategory::class)],
            'severity' => ['sometimes', 'required', 'string', Rule::enum(IncidentSeverity::class)],
            'status' => ['sometimes', 'required', 'string', Rule::enum(IncidentStatus::class)],
            'stage' => ['sometimes', 'required', 'string', Rule::enum(IncidentStage::class)],
            'ic_owner' => ['sometimes', 'required', 'string', 'max:255'],
            'ai_model_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_models,id'],
            'ai_model_version_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_model_versions,id'],
            'use_case_id' => ['sometimes', 'nullable', 'integer', 'exists:use_cases,id'],
            'first_seen_at' => ['sometimes', 'required', 'date'],
            'declared_at' => ['sometimes', 'required', 'date'],
            'resolved_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:declared_at'],
            'closed_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:resolved_at'],
            'impacted_users' => ['sometimes', 'nullable', 'string', 'max:255'],
            'impacted_data' => ['sometimes', 'required', 'array', 'min:1'],
            'impacted_data.*' => ['required', 'string', 'in:pii,sensitive_personal,financial,health,ip_copyright,none,unknown'],
            'impacted_systems' => ['sometimes', 'nullable', 'string'],
            'linked_release_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'linked_risk_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'linked_assessment_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'linked_capa_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'evidence_link' => ['sometimes', 'nullable', 'url', 'max:255'],
        ];
    }
}
