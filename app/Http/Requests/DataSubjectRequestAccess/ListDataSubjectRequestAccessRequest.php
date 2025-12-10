<?php

namespace App\Http\Requests\DataSubjectRequestAccess;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DataSubjectRequestAccess\Status;
use App\Enums\DataSubjectRequestAccess\Priority;
use App\Enums\DataSubjectRequestAccess\RequestType;
use App\Enums\DataSubjectRequestAccess\Jurisdiction;
use App\Enums\DataSubjectRequestAccess\SubjectRealm;
use App\Enums\DataSubjectRequestAccess\VerificationStatus;

class ListDataSubjectRequestAccessRequest extends FormRequest
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
            'status' => ['nullable', Rule::enum(Status::class)],
            'request_type' => ['nullable', Rule::enum(RequestType::class)],
            'verification_status' => ['nullable', Rule::enum(VerificationStatus::class)],
            'jurisdiction' => ['nullable', Rule::enum(Jurisdiction::class)],
            'subject_realm' => ['nullable', Rule::enum(SubjectRealm::class)],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
