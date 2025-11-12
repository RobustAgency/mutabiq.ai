<?php

namespace App\Http\Requests\AiModelDataset;

use App\Enums\AiModelDataset\EligibilityStatus;
use App\Enums\AiModelDataset\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiModelDatasetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ai_model_id' => ['required', 'integer', 'exists:ai_models,id'],
            'ai_model_version_id' => ['required', 'integer', 'exists:ai_model_versions,id'],
            'dataset_id' => ['required', 'integer', 'exists:datasets,id'],
            'dataset_snapshot_id' => [
                'nullable',
                'integer',
                'exists:dataset_snapshots,id',
                Rule::requiredIf(function () {
                    $role = $this->input('role');
                    return in_array($role, [
                        Role::TRAIN->value,
                        Role::VALIDATION->value,
                        Role::TEST->value,
                        Role::EVAL_BENCHMARK->value
                    ]);
                }),
            ],
            'role' => ['required', Rule::enum(Role::class)],
            'access_path' => ['nullable', 'string', 'max:500'],
            'transform_pack_link' => ['nullable', 'string', 'max:500'],
            'license_check_ref' => ['nullable', 'string', 'max:255'],
            'privacy_check_ref' => ['nullable', 'string', 'max:255'],
            'eligibility_status' => ['nullable', Rule::enum(EligibilityStatus::class)],
            'notes' => ['nullable', 'string'],
            'source_created_at' => ['required', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'dataset_snapshot_id.required' => 'A dataset snapshot is required for train, validation, test, and eval_benchmark roles for reproducibility and audit purposes.',
        ];
    }
}
