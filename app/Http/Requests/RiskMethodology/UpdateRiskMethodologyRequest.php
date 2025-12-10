<?php

namespace App\Http\Requests\RiskMethodology;

use Illuminate\Validation\Rule;
use App\Enums\RiskMethodology\ImpactScale;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RiskMethodology\LikelihoodScale;

class UpdateRiskMethodologyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'likelihood_scale' => ['sometimes', Rule::enum(LikelihoodScale::class)],
            'impact_scale' => ['sometimes', Rule::enum(ImpactScale::class)],
            'matrix_rule' => ['sometimes', 'array'],
            'acceptance_thresholds' => ['sometimes', 'nullable', 'string'],
            'aggregation_logic' => ['sometimes', 'string'],
            'review_policy' => ['sometimes', 'string'],
            'effective_from' => ['sometimes', 'date'],
            'effective_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:effective_from'],
            'owner_team' => ['sometimes', 'string', 'max:255'],
            'source_created_at' => ['sometimes', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'effective_to.after_or_equal' => 'The effective to date must be on or after the effective from date.',
        ];
    }
}
