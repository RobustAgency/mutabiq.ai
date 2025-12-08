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

class StoreRecordOfProcessingActivityRequest extends FormRequest
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
            'activity_code' => ['required', 'string', 'max:255', 'unique:record_of_processing_activities,activity_code'],
            'activity_name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string'],
            'detailed_purpose' => ['nullable', 'string'],
            'owner_team' => ['required', 'string', Rule::enum(OwnerTeam::class)],
            'controller_role' => ['required', 'string', Rule::enum(ControllerRole::class)],
            'data_subject_categories' => ['required', 'array', 'min:1'],
            'data_subject_categories.*' => ['string', Rule::enum(DataSubjectCategory::class)],
            'data_categories' => ['required', 'array', 'min:1'],
            'data_categories.*' => ['string', Rule::enum(DataCategory::class)],
            'contains_pii' => ['nullable', 'boolean'],
            'consent_required' => ['nullable', 'boolean'],
            'lawful_basis' => ['required', 'string', Rule::enum(LawfulBasis::class)],
            'legitimate_interest_assessment' => ['nullable', 'string'],
            'consent_coverage_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'dpia_required' => ['nullable', 'boolean'],
            'dpia_status' => ['nullable', 'string', Rule::enum(DPIAStatus::class)],
            'dpia_id' => ['nullable', 'integer'],
            'retention_period' => ['required', 'string', 'max:255'],
            'retention_justification' => ['required', 'string'],
            'has_international_transfers' => ['nullable', 'boolean'],
            'applicable_jurisdictions' => ['required', 'array', 'min:1'],
            'applicable_jurisdictions.*' => ['string', Rule::enum(ApplicableJurisdiction::class)],
            'security_measures' => ['required', 'string'],
            'internal_recipients' => ['nullable', 'array'],
            'internal_recipients.*' => ['nullable', 'max:255'],
            'external_recipients' => ['nullable', 'array'],
            'external_recipients.*' => ['string', 'max:255'],
            'status' => ['required', 'string', Rule::enum(Status::class)],
            'last_reviewed_date' => ['nullable', 'date'],
            'next_review_date' => ['nullable', 'date'],
        ];
    }
}
