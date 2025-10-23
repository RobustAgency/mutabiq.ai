<?php

namespace App\Http\Requests\DataSource;

use App\Enums\DataSource\AccessMethod;
use App\Enums\DataSource\CloudProvider;
use App\Enums\DataSource\DataClassification;
use App\Enums\DataSource\DataResidency;
use App\Enums\DataSource\HostingModel;
use App\Enums\DataSource\ServiceModel;
use App\Enums\DataSource\SystemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDataSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'system_type' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, SystemType::cases()))],
            'owner_team' => ['sometimes', 'string', 'max:255'],
            'data_domains' => ['sometimes', 'array'],
            'data_domains.*' => ['string'],
            'access_method' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, AccessMethod::cases()))],
            'residency' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, DataResidency::cases()))],
            'classification' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, DataClassification::cases()))],
            'hosting_model' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, HostingModel::cases()))],
            'service_model' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, ServiceModel::cases()))],
            'cloud_provider' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, CloudProvider::cases()))],
            'primary_region' => ['nullable', 'string', 'max:255'],
            'secondary_region' => ['nullable', 'string', 'max:255'],
            'network_ref' => ['nullable', 'string', 'max:255'],
            'retention_policy_ref' => ['nullable', 'string', 'max:255'],
            'catalog_uri' => ['nullable', 'string', 'max:255', 'url'],
        ];
    }
}
