<?php

namespace App\Http\Requests\RiskMethodology;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiskMethodologyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'likelihood_scale' => ['required', 'array'],
            'impact_scale' => ['required', 'array'],
            'matrix_rule' => ['required', 'array'],
            'acceptance_thresholds' => ['nullable', 'string'],
            'aggregation_logic' => ['nullable', 'string'],
            'review_policy' => ['required', 'string'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'owner_team' => ['required', 'string', 'max:255'],
            'source_created_at' => ['required', 'date'],
        ];
    }
}
