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
            'description' => ['sometimes', 'string'],
            'system_type' => ['sometimes', Rule::enum(SystemType::class)],
            'owner_team' => ['sometimes', Rule::enum(OwnerTeam::class)],
            'data_domains' => ['sometimes', 'array'],
            'data_domains.*' => ['required', Rule::enum(DataDomain::class)],
            'residency' => ['sometimes', Rule::enum(DataResidency::class)],
            'criticality_level' => ['sometimes', Rule::enum(CriticalityLevel::class)],
            'hosting_model' => ['sometimes', Rule::enum(HostingModel::class)],
            'technical_owner' => ['sometimes', Rule::enum(OwnerTeam::class)],
            'business_owner' => ['sometimes', Rule::enum(OwnerTeam::class)],
            'last_review_date' => ['sometimes', 'date'],
            'next_review_date' => ['sometimes', 'date', 'after_or_equal:last_review_date'],
            'status' => ['sometimes', Rule::enum(Status::class)],
        ];
    }
}
