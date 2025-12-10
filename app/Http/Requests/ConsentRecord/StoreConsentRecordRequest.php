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

class StoreConsentRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_key' => ['required', 'string', 'max:255'],
            'subject_realm' => ['required', Rule::enum(SubjectRealm::class)],
            'subject_age_group' => ['nullable', 'string', 'max:50'],
            'purpose' => ['required', Rule::enum(Purpose::class)],
            'record_of_processing_activity_id' => ['required', 'integer', 'exists:record_of_processing_activities,id'],
            'status' => ['required', Rule::enum(Status::class)],
            'lifecycle_stage' => ['required', Rule::enum(Lifecycle::class)],
            'consent_version' => ['required', 'integer', 'min:1'],
            'consent_text' => ['required', 'string'],
            'consent_method' => ['required', Rule::enum(Method::class)],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'last_refreshed_date' => ['nullable', 'date'],
            'source_system' => ['required', Rule::enum(SourceSystem::class)],
            'evidence_uri' => ['nullable', 'string', 'url'],
            'ip_address' => ['nullable', 'ip'],
            'user_agent' => ['nullable', 'string'],
            'language' => ['required', Rule::enum(Language::class)],
            'jurisdiction' => ['required', Rule::enum(Jurisdiction::class)],
            'data_categories' => ['required', 'array', 'min:1'],
            'data_categories.*' => [Rule::enum(DataCategory::class)],
            'can_withdraw' => ['required', 'boolean'],
            'withdrawal_method' => ['required', 'string', 'max:255'],
        ];
    }
}
