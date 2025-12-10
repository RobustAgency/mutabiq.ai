<?php

namespace App\Http\Requests\ConsentRecord;

use Illuminate\Validation\Rule;
use App\Enums\ConsentRecord\Status;
use App\Enums\ConsentRecord\Language;
use App\Enums\ConsentRecord\Lifecycle;
use App\Enums\ConsentRecord\Jurisdiction;
use App\Enums\ConsentRecord\SubjectRealm;
use Illuminate\Foundation\Http\FormRequest;

class ListConsentRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_realm' => ['nullable', Rule::enum(SubjectRealm::class)],
            'status' => ['nullable', Rule::enum(Status::class)],
            'lifecycle_stage' => ['nullable', Rule::enum(Lifecycle::class)],
            'language' => ['nullable', Rule::enum(Language::class)],
            'jurisdiction' => ['nullable', Rule::enum(Jurisdiction::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
