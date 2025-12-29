<?php

namespace App\Http\Requests\DataSource;

use Illuminate\Validation\Rule;
use App\Enums\DataSource\Status;
use App\Enums\DataSource\OwnerTeam;
use App\Enums\DataSource\DataDomain;
use App\Enums\DataSource\SystemType;
use App\Enums\DataSource\HostingModel;
use App\Enums\DataSource\DataResidency;
use App\Enums\DataSource\CriticalityLevel;
use Illuminate\Foundation\Http\FormRequest;

class StoreDataSourceRequest extends FormRequest
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
            'system_type' => ['required', Rule::enum(SystemType::class)],
            'owner_team' => ['required', Rule::enum(OwnerTeam::class)],
            'data_domains' => ['required', 'array'],
            'data_domains.*' => ['required', Rule::enum(DataDomain::class)],
            'residency' => ['required', Rule::enum(DataResidency::class)],
            'criticality_level' => ['nullable', Rule::enum(CriticalityLevel::class)],
            'hosting_model' => ['required', Rule::enum(HostingModel::class)],
            'technical_owner' => ['required', Rule::enum(OwnerTeam::class)],
            'business_owner' => ['required', Rule::enum(OwnerTeam::class)],
            'last_review_date' => ['nullable', 'date'],
            'next_review_date' => ['nullable', 'date', 'after_or_equal:last_review_date'],
            'status' => ['required', 'string', Rule::enum(Status::class)],
        ];
    }
}
