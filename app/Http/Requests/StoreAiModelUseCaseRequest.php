<?php

namespace App\Http\Requests;

use App\Enums\AiModelUseCase\DataSensitivity;
use App\Enums\AiModelUseCase\RiskLevel;
use App\Enums\AiModelUseCase\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\AiModelUseCase\RegulatoryScope;

class StoreAiModelUseCaseRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'business_objective' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_map(fn($c) => $c->value, Status::cases()))],
            'business_domain' => ['required', 'string', 'max:255'],
            'business_owner_email' => ['required', 'email', 'max:255'],
            'technical_owner_email' => ['required', 'email', 'max:255'],
            'regulatory_scope' => ['required', 'array'],
            'regulatory_scope.*' => [Rule::in(array_map(fn($c) => $c->value, RegulatoryScope::cases()))],
            'data_sensitivity' => ['required', 'string', Rule::in(array_map(fn($c) => $c->value, DataSensitivity::cases()))],
            'go_live_date' => ['nullable', 'date'],
            'expected_roi' => ['nullable', 'numeric'],
            'implementation_cost' => ['nullable', 'numeric'],
            'reduction_in_time' => ['nullable', 'numeric'],
            'reduction_in_cost' => ['nullable', 'numeric'],
            'increase_in_revenue' => ['nullable', 'numeric'],
            'risk_avoidance' => ['nullable', 'numeric'],
            'fte_capacity_saved' => ['nullable', 'numeric'],
            'use_case_type' => ['required', 'string', 'max:255'],
            'value_driver' => ['required', 'string', 'max:255'],
            'risk_level' => ['required', 'string', Rule::in(array_map(fn($c) => $c->value, RiskLevel::cases()))],
            'overall_risk_score' => ['nullable', 'numeric'],
            'human_oversight_mode' => ['required', 'string', 'max:255'],
            'dpia' => ['nullable', 'boolean'],
            'aia' => ['nullable', 'boolean'],
            'data_availability_status' => ['required', 'string', 'max:255'],
            'data_readiness_level' => ['required', 'string', 'max:255'],
            'data_freshness' => ['required', 'string', 'max:255'],
        ];
    }
}
