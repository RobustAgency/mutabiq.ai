<?php

namespace App\Http\Requests\AiModelDataset;

use Illuminate\Validation\Rule;
use App\Enums\AiModelDataset\Role;
use App\Enums\AiModelDataset\CreatedBy;
use App\Enums\AiModelDataset\LinkageStatus;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AiModelDataset\CrossBorderCheck;
use App\Enums\AiModelDataset\ConsentCheckStatus;
use App\Enums\AiModelDataset\SpecialCategoryCheck;

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
                        Role::EVAL_BENCHMARK->value,
                    ]);
                }),
            ],
            'role' => ['required', Rule::enum(Role::class)],
            'rows_used' => ['nullable', 'integer', 'min:0'],
            'training_start_date' => ['nullable', 'date'],
            'training_end_date' => ['nullable', 'date', 'after_or_equal:training_start_date'],
            'training_duration' => ['nullable', 'string', 'max:100'],
            'compute_resources' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'consent_check_status' => ['nullable', Rule::enum(ConsentCheckStatus::class)],
            'cross_border_check' => ['required', Rule::enum(CrossBorderCheck::class)],
            'special_category_check' => ['required', Rule::enum(SpecialCategoryCheck::class)],
            'bias_mitigation_applied' => ['nullable', 'boolean'],
            'created_by_system' => ['required', Rule::enum(CreatedBy::class)],
            'linkage_status' => ['required', Rule::enum(LinkageStatus::class)],
            'business_justification' => ['nullable', 'string'],
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
