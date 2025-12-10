<?php

namespace App\Http\Requests\ConsentRecord;

use Illuminate\Validation\Rule;
use App\Enums\ConsentRecord\Method;
use App\Enums\ConsentRecord\Status;
use App\Enums\ConsentRecord\Purpose;
use App\Enums\ConsentRecord\Language;
use App\Enums\ConsentRecord\Lifecycle;
use App\Enums\ConsentRecord\Jurisdiction;
use App\Enums\ConsentRecord\SourceSystem;
use App\Enums\ConsentRecord\SubjectRealm;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RecordOfProcessingActivity\DataCategory;

class UpdateConsentRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_key' => ['sometimes', 'string', 'max:255'],
            'subject_realm' => ['sometimes', Rule::enum(SubjectRealm::class)],
            'subject_age_group' => ['sometimes', 'nullable', 'string', 'max:50'],
            'purpose' => ['sometimes', Rule::enum(Purpose::class)],
            'record_of_processing_activity_id' => ['sometimes', 'integer', 'exists:record_of_processing_activities,id'],
            'status' => ['sometimes', Rule::enum(Status::class)],
            'lifecycle_stage' => ['sometimes', Rule::enum(Lifecycle::class)],
            'consent_version' => ['sometimes', 'integer', 'min:1'],
            'consent_text' => ['sometimes', 'string'],
            'consent_method' => ['sometimes', Rule::enum(Method::class)],
            'effective_from' => ['sometimes', 'date'],
            'effective_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:effective_from'],
            'last_refreshed_date' => ['sometimes', 'nullable', 'date'],
            'source_system' => ['sometimes', Rule::enum(SourceSystem::class)],
            'evidence_uri' => ['sometimes', 'nullable', 'string', 'url'],
            'ip_address' => ['sometimes', 'nullable', 'ip'],
            'user_agent' => ['sometimes', 'nullable', 'string'],
            'language' => ['sometimes', Rule::enum(Language::class)],
            'jurisdiction' => ['sometimes', Rule::enum(Jurisdiction::class)],
            'data_categories' => ['sometimes', 'array', 'min:1'],
            'data_categories.*' => [Rule::enum(DataCategory::class)],
            'can_withdraw' => ['sometimes', 'boolean'],
            'withdrawal_method' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
