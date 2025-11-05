<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ComplexityLevel;
use App\Enums\DeploymentStatus;
use App\Enums\LifecycleStage;
use App\Enums\ValidationStatus;
use App\Enums\ComplianceStatus;
use App\Enums\VersionType;
use Illuminate\Validation\Rule;

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
            'version_type' => ['required', Rule::in(array_map(fn($c) => $c->value, VersionType::cases()))],
            'ai_model_id' => ['required', 'exists:ai_models,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'version_role' => ['nullable', 'string', 'max:255'],
            'version_source' => ['nullable', 'string', 'max:255'],
            'our_involvement' => ['nullable', 'string', 'max:255'],
            'release_date' => ['nullable', 'date'],
            'release_notes' => ['nullable', 'string'],

            // Technical characteristics
            'architecture_type' => ['required', 'string', 'max:255'],
            'model_file_size_gb' => ['nullable', 'numeric', 'min:0'],
            'training_duration_hours' => ['nullable', 'integer', 'min:0'],
            'complexity_level' => ['required', Rule::in(array_map(fn($c) => $c->value, ComplexityLevel::cases()))],
            'parameter_count' => ['nullable', 'integer', 'min:0'],

            // Modalities (stored as JSON)
            'input_modalities' => ['nullable', 'array'],
            'input_modalities.*' => ['string', Rule::in(['text', 'image', 'audio', 'video', 'structured_data', 'time_series'])],
            'output_modalities' => ['nullable', 'array'],
            'output_modalities.*' => ['string', Rule::in(['text', 'image', 'audio', 'classification', 'regression', 'embedding', 'structured_data'])],

            // Deployment / lifecycle / compliance
            'deployment_status' => ['required', Rule::in(array_map(fn($c) => $c->value, DeploymentStatus::cases()))],
            'lifecycle_stage' => ['required', Rule::in(array_map(fn($c) => $c->value, LifecycleStage::cases()))],
            'deployment_environments' => ['nullable', 'array'],
            'deployment_environments.*' => ['string', 'max:100'],

            // Flags
            'has_performance_data' => ['required', 'boolean'],

            'created_by' => ['required', 'email'],
            'updated_by' => ['nullable', 'email'],
        ];
    }
}
