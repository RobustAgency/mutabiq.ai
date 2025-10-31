<?php

namespace App\Http\Requests;

use App\Enums\UseCase\BusinessDomain;
use App\Enums\UseCase\DataAvailabilityStatus;
use App\Enums\UseCase\DataReadiness;
use App\Enums\UseCase\DataSensitivity;
use App\Enums\UseCase\Priority;
use App\Enums\UseCase\RiskLevel;
use App\Enums\UseCase\ROIClassification;
use App\Enums\UseCase\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'description' => ['required', 'string', 'min:100', 'max:5000'],
            'business_objective' => ['required', 'string', 'min:50', 'max:2000'],
            'business_owner_id' => ['nullable', 'integer', 'exists:stakeholders,id'],
            'technical_owner_id' => ['nullable', 'integer', 'exists:stakeholders,id'],
            'business_domain' => ['required', Rule::enum(BusinessDomain::class)],
            'roi_classification' => ['nullable', Rule::enum(ROIClassification::class)],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'risk_level' => ['required', Rule::enum(RiskLevel::class)],
            'data_sensitivity' => ['required', Rule::enum(DataSensitivity::class)],
            'expected_roi_percentage' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'budget_allocated' => ['nullable', 'numeric', 'min:0'],
            'target_go_live_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(Status::class)],
            'created_by' => ['required', 'email'],
            'updated_by' => ['nullable', 'email'],
            'roi_assessment' => ['nullable', 'boolean'],
            'risk_assessment' => ['nullable', 'boolean'],
            'data_assessment' => ['nullable', 'boolean'],
            'estimated_implementation_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_reduction_in_time' => ['nullable', 'numeric', 'min:0'],
            'estimated_reduction_in_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_revenue_increase' => ['nullable', 'numeric', 'min:0'],
            'estimated_fte_capacity_saving' => ['nullable', 'integer', 'min:0'],
            'data_availability_status' => ['nullable', Rule::enum(DataAvailabilityStatus::class)],
            'data_readiness' => ['nullable', Rule::enum(DataReadiness::class)],
        ];
    }
}
