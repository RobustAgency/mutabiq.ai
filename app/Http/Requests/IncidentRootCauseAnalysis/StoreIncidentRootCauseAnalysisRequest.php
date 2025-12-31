<?php

namespace App\Http\Requests\IncidentRootCauseAnalysis;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\IncidentRootCauseAnalysis\RcaMethod;

class StoreIncidentRootCauseAnalysisRequest extends FormRequest
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
            'ai_incident_id' => ['required', 'integer', 'exists:ai_incidents,id'],
            'rca_method' => ['required', Rule::enum(RcaMethod::class)],
            'analysis_date' => ['nullable', 'date'],
            'immediate_cause' => ['required', 'string'],
            'root_causes' => ['required', 'string'],
            'contributing_factors' => ['nullable', 'string'],
            'control_failures' => ['nullable', 'string'],
            'recommendations' => ['required', 'string'],
            'lead_analyst' => ['required', 'string', 'max:255'],
            'review_committee' => ['nullable', 'string'],
            'approved_at' => ['nullable', 'date'],
            'report_link' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
