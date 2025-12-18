<?php

namespace App\Http\Requests\DataSubjectRequestAccess;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DataSubjectRequestAccess\Status;
use App\Enums\DataSubjectRequestAccess\Priority;
use App\Enums\DataSubjectRequestAccess\RequestType;
use App\Enums\DataSubjectRequestAccess\SubjectRealm;
use App\Enums\DataSubjectRequestAccess\RequestSource;
use App\Enums\DataSubjectRequestAccess\ResponseFormat;
use App\Enums\DataSubjectRequestAccess\ResponseMethod;
use App\Enums\DataSubjectRequestAccess\VerificationMethod;
use App\Enums\DataSubjectRequestAccess\VerificationStatus;

class UpdateDataSubjectRequestAccessRequest extends FormRequest
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
        $isVerified = fn () => $this->verification_status === VerificationStatus::VERIFIED;
        $isCompleted = fn () => $this->status === Status::COMPLETED;
        $isReadyForResponse = fn () => $this->status === Status::READY_FOR_RESPONSE;
        $isRejected = fn () => $this->status === Status::REJECTED;

        return [
            'request_type' => ['sometimes', Rule::enum(RequestType::class)],
            'subject_identifier' => ['sometimes', 'string', 'max:255'],
            'subject_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'subject_realm' => ['sometimes', Rule::enum(SubjectRealm::class)],
            'verification_status' => ['sometimes', Rule::enum(VerificationStatus::class)],
            'subject_key' => [
                'sometimes',
                Rule::requiredIf($isVerified),
                'string',
                'max:255',
            ],
            'verification_method' => [
                'sometimes',
                Rule::requiredIf($isVerified),
                Rule::enum(VerificationMethod::class),
            ],
            'verification_date' => ['sometimes', 'nullable', 'date'],
            'verified_by' => [
                'sometimes',
                Rule::requiredIf($isVerified),
                'integer',
                'exists:users,id',
            ],
            'request_details' => ['sometimes', 'string'],
            'requested_data_categories' => ['sometimes', 'nullable', 'array'],
            'requested_data_categories.*' => ['string', 'max:255'],
            'request_source' => ['sometimes', Rule::enum(RequestSource::class)],
            'submitted_date' => ['sometimes', 'date'],
            'due_date' => ['sometimes', 'date', 'after_or_equal:submitted_date'],
            'extended_due_date' => ['sometimes', 'nullable', 'date', 'after:due_date'],
            'response_date' => [
                'sometimes',
                Rule::requiredIf($isCompleted),
                'date',
            ],
            'completed_date' => [
                'sometimes',
                Rule::requiredIf($isCompleted),
                'date',
                'after_or_equal:response_date',
            ],
            'status' => ['sometimes', Rule::enum(Status::class)],
            'priority' => ['sometimes', Rule::enum(Priority::class)],
            'is_overdue' => ['sometimes', 'boolean'],
            'assigned_to' => ['sometimes', 'integer', 'exists:users,id'],
            'assigned_date' => ['sometimes', 'date'],
            'response_method' => [
                'sometimes',
                Rule::requiredIf($isReadyForResponse),
                Rule::enum(ResponseMethod::class),
            ],
            'response_format' => [
                'sometimes',
                Rule::requiredIf($isReadyForResponse),
                Rule::enum(ResponseFormat::class),
            ],
            'response_uri' => [
                'sometimes',
                Rule::requiredIf($isReadyForResponse),
                'url',
            ],
            'response_notes' => ['sometimes', 'nullable', 'string'],
            'rejection_reason' => ['sometimes', Rule::requiredIf($isRejected), 'string'],
            'jurisdiction' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'processing_activity_ids' => ['sometimes', 'nullable', 'array'],
            'processing_activity_ids.*' => ['integer', 'exists:record_of_processing_activities,id'],
            'systems_checked' => ['sometimes', 'array', 'min:1'],
            'systems_checked.*' => ['string', 'max:255'],
            'records_found' => ['sometimes', 'nullable', 'integer'],
        ];
    }
}
