<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\PrimaryCategory;
use App\Enums\OperationalStatus;
use App\Enums\BusinessStatus;
use App\Enums\OwnershipType;
use App\Enums\DevelopmentSource;
use App\Enums\OrganizationalRole;

class StoreAiModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'source_org_stakeholder_id' => [
                'required',
                Rule::exists('stakeholders', 'id'),
            ],
            'owner_stakeholder_id' => [
                'required',
                Rule::exists('stakeholders', 'id'),
            ],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'primary_category' => ['required', Rule::in(array_map(fn($c) => $c->value, PrimaryCategory::cases()))],
            'type' => ['required', 'string', 'max:255'],
            'domain_specialization' => ['required', 'string', 'max:255'],
            'operational_status' => ['required', Rule::in(array_map(fn($c) => $c->value, OperationalStatus::cases()))],
            'business_status' => ['required', Rule::in(array_map(fn($c) => $c->value, BusinessStatus::cases()))],
            'regulatory_risk_classification' => ['nullable', 'string', 'max:255'],
            'ownership_type' => ['required', Rule::in(array_map(fn($c) => $c->value, OwnershipType::cases()))],
            'development_source' => ['required', Rule::in(array_map(fn($c) => $c->value, DevelopmentSource::cases()))],
            'current_version_id' => ['required_if:operational_status,production', 'exists:ai_model_versions,id'],
            'organizational_role' => ['required', 'string', Rule::in(array_map(fn($c) => $c->value, OrganizationalRole::cases()))],
            'creator_email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'owner_stakeholder_id.required' => 'The model owner is required.',
            'owner_stakeholder_id.exists' => 'The selected model owner is invalid.',
        ];
    }
}
