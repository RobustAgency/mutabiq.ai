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

class StoreDataSubjectRequestAccessRequest extends FormRequest
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
            'request_type' => ['required', Rule::enum(RequestType::class)],
            'subject_identifier' => ['required', 'string', 'max:255'],
            'subject_name' => ['nullable', 'string', 'max:255'],
            'subject_realm' => ['required', Rule::enum(SubjectRealm::class)],
            'verification_status' => ['required', Rule::enum(VerificationStatus::class)],
            'subject_key' => [
                Rule::requiredIf($isVerified),
                'string',
                'max:255',
            ],
            'verification_method' => [
                Rule::requiredIf($isVerified),
                Rule::enum(VerificationMethod::class),
            ],
            'verified_by' => [
                Rule::requiredIf($isVerified),
                'integer',
                'exists:users,id',
            ],
            'request_details' => ['required', 'string'],
            'requested_data_categories' => ['nullable', 'array'],
            'requested_data_categories.*' => ['string', 'max:255'],
            'request_source' => ['required', Rule::enum(RequestSource::class)],
            'submitted_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:submitted_date'],
            'extended_due_date' => ['nullable', 'date', 'after:due_date'],
            'status' => ['required', Rule::enum(Status::class)],
            'response_date' => [
                Rule::requiredIf($isCompleted),
                'date',
            ],
            'completed_date' => [
                Rule::requiredIf($isCompleted),
                'date',
                'after_or_equal:response_date',
            ],
            'priority' => ['required', Rule::enum(Priority::class)],
            'is_overdue' => ['required', 'boolean'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'assigned_date' => ['required', 'date'],
            'response_method' => [
                Rule::requiredIf($isReadyForResponse),
                Rule::enum(ResponseMethod::class),
            ],
            'response_format' => [
                Rule::requiredIf($isReadyForResponse),
                Rule::enum(ResponseFormat::class),
            ],
            'response_uri' => [
                Rule::requiredIf($isReadyForResponse),
                'url',
            ],
            'response_notes' => ['nullable', 'string'],
            'rejection_reason' => [Rule::requiredIf($isRejected), 'string'],
            'jurisdiction' => [
                'string',
                'max:255',
            ],
            'processing_activity_ids' => ['nullable', 'array'],
            'processing_activity_ids.*' => ['integer', 'exists:record_of_processing_activities,id'],
            'systems_checked' => ['required', 'array', 'min:1'],
            'systems_checked.*' => ['string', 'max:255'],
            'records_found' => ['nullable', 'integer'],
        ];
    }
}
