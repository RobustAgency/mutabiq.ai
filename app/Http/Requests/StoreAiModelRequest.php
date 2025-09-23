<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\PrimaryCategory;
use App\Enums\OperationalStatus;
use App\Enums\BusinessStatus;
use App\Enums\StrategicImportance;
use App\Enums\OrganizationalRole;
use App\Enums\OwnershipType;
use App\Enums\DevelopmentSource;

class StoreAiModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'primary_category' => ['required', Rule::in(array_map(fn($c) => $c->value, PrimaryCategory::cases()))],
            'type' => ['required', 'string','max:255'],
            'domain_specialization' => ['required', 'string','max:255'],
            'operational_status' => ['required', Rule::in(array_map(fn($c) => $c->value, OperationalStatus::cases()))],
            'business_status' => ['required', Rule::in(array_map(fn($c) => $c->value, BusinessStatus::cases()))],
            'strategic_importance' => ['required', Rule::in(array_map(fn($c) => $c->value, StrategicImportance::cases()))],
            'regulatory_risk_classification' => ['required', 'string','max:255'],
            'organizational_role' => ['required', Rule::in(array_map(fn($c) => $c->value, OrganizationalRole::cases()))],
            'ownership_type' => ['required', Rule::in(array_map(fn($c) => $c->value, OwnershipType::cases()))],
            'development_source' => ['required', Rule::in(array_map(fn($c) => $c->value, DevelopmentSource::cases()))],
            'source_organization' => ['required','string','max:255'],
            'current_owner' => ['required','string','max:255'],
        ];
    }
}