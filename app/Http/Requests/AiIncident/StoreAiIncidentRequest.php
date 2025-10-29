<?php

namespace App\Http\Requests\AiIncident;

use App\Enums\IncidentCategory;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStage;
use App\Enums\IncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'category' => ['required', 'string', Rule::enum(IncidentCategory::class)],
            'severity' => ['required', 'string', Rule::enum(IncidentSeverity::class)],
            'status' => ['required', 'string', Rule::enum(IncidentStatus::class)],
            'stage' => ['required', 'string', Rule::enum(IncidentStage::class)],
            'ic_owner' => ['required', 'string', 'max:255'],
            'ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
            'ai_model_version_id' => ['nullable', 'integer', 'exists:ai_model_versions,id'],
            'use_case_id' => ['nullable', 'integer', 'exists:use_cases,id'],
            'first_seen_at' => ['required', 'date'],
            'declared_at' => ['required', 'date'],
            'resolved_at' => ['nullable', 'date', 'after_or_equal:declared_at'],
            'closed_at' => ['nullable', 'date', 'after_or_equal:resolved_at'],
            'impacted_users' => ['nullable', 'string', 'max:255'],
            'impacted_data' => ['required', 'array', 'min:1'],
            'impacted_data.*' => ['required', 'string', 'in:pii,sensitive_personal,financial,health,ip_copyright,none,unknown'],
            'impacted_systems' => ['nullable', 'string'],
            'linked_release_id' => ['nullable', 'string', 'max:255'],
            'linked_risk_id' => ['nullable', 'string', 'max:255'],
            'linked_assessment_id' => ['nullable', 'string', 'max:255'],
            'linked_capa_id' => ['nullable', 'string', 'max:255'],
            'evidence_link' => ['nullable', 'url', 'max:255'],
        ];
    }
}
