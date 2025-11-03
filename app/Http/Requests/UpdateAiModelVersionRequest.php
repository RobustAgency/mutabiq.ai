<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ComplexityLevel;
use App\Enums\DeploymentStatus;
use App\Enums\LifecycleStage;
use App\Enums\ValidationStatus;
use App\Enums\ComplianceStatus;
use App\Enums\VersionType;

class UpdateAiModelVersionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // allow, enforce via policies elsewhere
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'version_number' => ['sometimes', 'string', 'max:255'],
            'version_type' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, VersionType::cases()))],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'version_role' => ['sometimes', 'nullable', 'string', 'max:255'],
            'version_source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'our_involvement' => ['sometimes', 'nullable', 'string', 'max:255'],
            'release_date' => ['sometimes', 'nullable', 'date'],
            'release_notes' => ['sometimes', 'nullable', 'string'],

            'architecture_type' => ['sometimes', 'string', 'max:255'],
            'model_file_size_gb' => ['sometimes', 'numeric', 'min:0'],
            'training_duration_hours' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'complexity_level' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, ComplexityLevel::cases()))],
            'parameter_count' => ['sometimes', 'nullable', 'integer', 'min:0'],

            'input_modalities' => ['nullable', 'array'],
            'input_modalities.*' => ['string', Rule::in(['text', 'image', 'audio', 'video', 'structured_data', 'time_series'])],
            'output_modalities' => ['nullable', 'array'],
            'output_modalities.*' => ['string', Rule::in(['text', 'image', 'audio', 'classification', 'regression', 'embedding', 'structured_data'])],


            'deployment_status' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, DeploymentStatus::cases()))],
            'lifecycle_stage' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, LifecycleStage::cases()))],
            'deployment_environments' => ['sometimes', 'nullable', 'array'],
            'deployment_environments.*' => ['string', 'max:100'],
            'has_performance_data' => ['sometimes', 'boolean'],
        ];
    }
}
