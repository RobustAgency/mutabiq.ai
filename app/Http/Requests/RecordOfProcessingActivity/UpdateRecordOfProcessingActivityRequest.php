<?php

namespace App\Http\Requests\RecordOfProcessingActivity;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RecordOfProcessingActivity\Status;
use App\Enums\RecordOfProcessingActivity\OwnerTeam;
use App\Enums\RecordOfProcessingActivity\DPIAStatus;
use App\Enums\RecordOfProcessingActivity\LawfulBasis;
use App\Enums\RecordOfProcessingActivity\DataCategory;
use App\Enums\RecordOfProcessingActivity\ControllerRole;
use App\Enums\RecordOfProcessingActivity\DataSubjectCategory;
use App\Enums\RecordOfProcessingActivity\ApplicableJurisdiction;

class UpdateRecordOfProcessingActivityRequest extends FormRequest
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
            'activity_name' => ['sometimes', 'string', 'max:255'],
            'purpose' => ['sometimes', 'string'],
            'detailed_purpose' => ['nullable', 'string'],
            'owner_team' => ['sometimes', 'string', Rule::enum(OwnerTeam::class)],
            'controller_role' => ['sometimes', 'string', Rule::enum(ControllerRole::class)],
            'data_subject_categories' => ['sometimes', 'array', 'min:1'],
            'data_subject_categories.*' => ['string', Rule::enum(DataSubjectCategory::class)],
            'data_categories' => ['sometimes', 'array', 'min:1'],
            'data_categories.*' => ['string', Rule::enum(DataCategory::class)],
            'contains_pii' => ['sometimes', 'boolean'],
            'consent_required' => ['sometimes', 'boolean'],
            'lawful_basis' => ['sometimes', 'string', Rule::enum(LawfulBasis::class)],
            'legitimate_interest_assessment' => ['nullable', 'string'],
            'consent_coverage_percent' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'dpia_required' => ['sometimes', 'boolean'],
            'dpia_status' => ['sometimes', 'string', Rule::enum(DPIAStatus::class)],
            'dpia_id' => ['nullable', 'string', 'uuid'],
            'retention_period' => ['sometimes', 'string', 'max:255'],
            'retention_justification' => ['sometimes', 'string'],
            'has_international_transfers' => ['sometimes', 'boolean'],
            'applicable_jurisdictions' => ['sometimes', 'array', 'min:1'],
            'applicable_jurisdictions.*' => ['string', Rule::enum(ApplicableJurisdiction::class)],

            'linked_dataset_ids' => ['sometimes', 'nullable', 'array', 'distinct'],
            'linked_dataset_ids.*' => ['integer', 'exists:datasets,id'],

            'linked_ai_models_ids' => ['sometimes', 'nullable', 'array', 'distinct'],
            'linked_ai_models_ids.*' => ['integer', 'exists:ai_models,id'],

            'security_measures' => ['sometimes', 'string'],
            'internal_recipients' => ['sometimes', 'array'],
            'internal_recipients.*' => ['string', 'max:255'],
            'external_recipients' => ['nullable', 'array'],
            'external_recipients.*' => ['string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::enum(Status::class)],
            'last_reviewed_date' => ['nullable', 'date'],
            'next_review_date' => ['nullable', 'date'],
        ];
    }
}
