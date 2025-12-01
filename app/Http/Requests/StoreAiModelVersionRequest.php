<?php

namespace App\Http\Requests;

use App\Enums\VersionType;
use App\Enums\LifecycleStage;
use App\Enums\ComplexityLevel;
use App\Enums\DeploymentStatus;
use Illuminate\Validation\Rule;
use App\Enums\VersionSourceType;
use App\Enums\VersionReleaseRole;
use App\Enums\VersionApprovalStatus;
use App\Enums\VersionOrgInvolvement;
use App\Enums\VersionArchitectureType;
use App\Enums\VersionDeploymentEnvironment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAiModelVersionRequest extends FormRequest
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
            // Core identifiers
            'version_number' => ['required', 'string', 'max:255'],
            'version_type' => ['required', Rule::in(array_map(fn ($c) => $c->value, VersionType::cases()))],
            'ai_model_id' => ['required', 'exists:ai_models,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'org_involvement' => ['nullable', Rule::in(array_map(fn ($c) => $c->value, VersionOrgInvolvement::cases()))],
            'release_date' => ['nullable', 'date'],
            'release_notes' => ['nullable', 'string'],
            'release_role' => ['nullable', Rule::in(array_map(fn ($c) => $c->value, VersionReleaseRole::cases()))],
            'source_type' => ['nullable', Rule::in(array_map(fn ($c) => $c->value, VersionSourceType::cases()))],

            // Technical characteristics
            'architecture_type' => ['required', Rule::in(array_map(fn ($c) => $c->value, VersionArchitectureType::cases()))],
            'model_file_size_gb' => ['nullable', 'numeric', 'min:0'],
            'training_duration_hours' => ['nullable', 'integer', 'min:0'],
            'complexity_level' => ['nullable', Rule::in(array_map(fn ($c) => $c->value, ComplexityLevel::cases()))],
            'parameter_count' => ['nullable', 'integer', 'min:0'],

            // Modalities (stored as JSON)
            'input_modalities' => ['nullable', 'array'],
            'input_modalities.*' => ['string', Rule::in(['text', 'image', 'audio', 'video', 'structured_data', 'time_series'])],
            'output_modalities' => ['nullable', 'array'],
            'output_modalities.*' => ['string', Rule::in(['text', 'image', 'audio', 'classification', 'regression', 'embedding', 'structured_data'])],

            // Deployment / lifecycle / compliance
            'deployment_status' => ['required', Rule::in(array_map(fn ($c) => $c->value, DeploymentStatus::cases()))],
            'lifecycle_stage' => ['required', Rule::in(array_map(fn ($c) => $c->value, LifecycleStage::cases()))],
            'deployment_environments' => ['nullable', 'array'],
            'deployment_environments.*' => ['string', Rule::in(array_map(fn ($c) => $c->value, VersionDeploymentEnvironment::cases()))],
            'customizations_applied' => ['nullable', 'array'],
            'customizations_applied.*' => ['string'],
            'approval_status' => ['required_unless:deployment_status,'.DeploymentStatus::PRODUCTION->value, Rule::in(array_map(fn ($c) => $c->value, VersionApprovalStatus::cases()))],
        ];
    }
}
