<?php

namespace App\Http\Requests;

use App\Enums\OwnershipType;
use App\Enums\BusinessStatus;
use App\Enums\PrimaryCategory;
use Illuminate\Validation\Rule;
use App\Enums\OrganizationalRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreAiModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:ai_models,name'],
            'category' => ['required', Rule::in(array_map(fn ($c) => $c->value, PrimaryCategory::cases()))],
            'type' => ['required', 'string', 'max:255'],
            'ownership_category' => ['required', Rule::in(array_map(fn ($c) => $c->value, OwnershipType::cases()))],
            'responsible_org_role' => ['required', 'string', Rule::in(array_map(fn ($c) => $c->value, OrganizationalRole::cases()))],
            'technical_domain' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'criticality_level' => ['nullable', 'string', 'max:100'],
            'regulatory_risk_tier' => ['nullable', 'string', 'max:255'],
            'eu_ai_category' => ['nullable', 'string', 'max:255'],
            'business_owner_id' => ['nullable', 'exists:users,id'],
            'custodian_id' => ['nullable', 'exists:users,id'],
            'business_adoption_status' => ['nullable', Rule::in(array_map(fn ($c) => $c->value, BusinessStatus::cases()))],
        ];
    }
}
