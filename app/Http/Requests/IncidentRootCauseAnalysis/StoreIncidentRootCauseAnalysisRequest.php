<?php

namespace App\Http\Requests\IncidentRootCauseAnalysis;

use App\Enums\IncidentRootCauseAnalysis\RcaMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'rca_method' => ['required', Rule::in(array_map(fn($c) => $c->value, RcaMethod::cases()))],
            'immediate_cause' => ['required', 'string'],
            'latent_causes' => ['required', 'string'],
            'contributing_factors' => ['nullable', 'string'],
            'impact_assessment' => ['nullable', 'string'],
            'fixes_implemented' => ['nullable', 'string'],
            'lessons_learned' => ['required', 'string'],
            'recommendations' => ['required', 'string'],
            'approved_by' => ['required', 'string', 'max:255'],
            'approved_at' => ['required', 'date'],
            'report_link' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
