<?php

namespace App\Http\Requests\IncidentRootCauseAnalysis;

use App\Enums\IncidentRootCauseAnalysis\RcaMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentRootCauseAnalysisRequest extends FormRequest
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
            'ai_incident_id' => ['sometimes', 'required', 'integer', 'exists:ai_incidents,id'],
            'rca_method' => ['sometimes', 'required', Rule::in(array_map(fn($c) => $c->value, RcaMethod::cases()))],
            'immediate_cause' => ['sometimes', 'required', 'string'],
            'latent_causes' => ['sometimes', 'required', 'string'],
            'contributing_factors' => ['nullable', 'string'],
            'impact_assessment' => ['nullable', 'string'],
            'fixes_implemented' => ['nullable', 'string'],
            'lessons_learned' => ['sometimes', 'required', 'string'],
            'recommendations' => ['sometimes', 'required', 'string'],
            'approved_by' => ['sometimes', 'required', 'string', 'max:255'],
            'approved_at' => ['sometimes', 'required', 'date'],
            'report_link' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
