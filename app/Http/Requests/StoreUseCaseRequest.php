<?php

namespace App\Http\Requests;

use App\Enums\UseCase\Status;
use App\Enums\UseCase\Priority;
use Illuminate\Validation\Rule;
use App\Enums\UseCase\RiskLevel;
use App\Enums\UseCase\DataReadiness;
use App\Enums\UseCase\BusinessDomain;
use App\Enums\UseCase\HumanOversight;
use App\Enums\UseCase\DataSensitivity;
use App\Enums\UseCase\ROIClassification;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UseCase\DataAvailabilityStatus;

class StoreUseCaseRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'min:100', 'max:5000'],
            'problem_statement' => ['required', 'string', 'min:50', 'max:2000'],
            'expected_business_value' => ['required', 'string', 'min:50', 'max:2000'],
            'stakeholder_ids' => ['required', 'array'],
            'stakeholder_ids.*' => ['integer', 'exists:stakeholders,id'],
            'status' => ['nullable', Rule::enum(Status::class)],
            'business_domain' => ['nullable', Rule::enum(BusinessDomain::class)],
            'roi_classification' => ['nullable', Rule::enum(ROIClassification::class)],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'data_sensitivity' => ['required', Rule::enum(DataSensitivity::class)],
            'expected_roi' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'estimated_time_savings' => ['required', 'numeric', 'min:0'],
            'estimated_cost_savings' => ['required', 'numeric', 'min:0'],
            'estimated_revenue_impact' => ['required', 'numeric', 'min:0'],
            'success_metrics' => ['required', 'string', 'min:50', 'max:2000'],
            'preliminary_risk_level' => ['required', Rule::enum(RiskLevel::class)],
            'regulatory_impact' => ['required', 'in:yes,no'],
            'potential_harm' => ['required', 'string', 'min:50', 'max:2000'],
            'human_oversight_mode' => ['required', Rule::enum(HumanOversight::class)],
            'dependencies' => ['required', 'string', 'min:0', 'max:2000'],
            'budget_allocated' => ['nullable', 'numeric', 'min:0'],
            'target_deployment_date' => ['nullable', 'date'],
            'estimated_fte_saving' => ['required', 'integer', 'min:0'],
            'data_availability_status' => ['required', Rule::enum(DataAvailabilityStatus::class)],
            'data_readiness' => ['nullable', Rule::enum(DataReadiness::class)],
            'business_owner_id' => ['nullable', 'integer', 'exists:stakeholders,id'],
            'technical_owner_id' => ['nullable', 'integer', 'exists:stakeholders,id'],
        ];
    }
}
