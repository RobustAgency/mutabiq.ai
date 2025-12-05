<?php

namespace App\Http\Requests\RegulatorySubmission;

use Illuminate\Validation\Rule;
use App\Enums\RegulatorySubmission\Status;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RegulatorySubmission\SubmissionType;

class UpdateRegulatorySubmissionRequest extends FormRequest
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
            'framework_id' => ['sometimes', 'nullable', 'integer', 'exists:frameworks,id'],
            'ai_model_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_models,id'],
            'authority' => ['sometimes', 'required', 'string', 'max:255'],
            'jurisdiction' => ['sometimes', 'required', 'array', 'min:1'],
            'jurisdiction.*' => ['string', 'max:255'],
            'submission_type' => ['sometimes', 'required', 'string', Rule::enum(SubmissionType::class)],
            'content_summary' => ['sometimes', 'required', 'string', 'max:5000'],
            'tracking_id' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('regulatory_submissions')->ignore($this->regulatorySubmission)],
            'status' => ['sometimes', 'required', 'string', Rule::enum(Status::class)],
            'commitments' => ['sometimes', 'required', 'array'],
            'commitments.*' => ['string', 'max:1000'],
            'renewal_due_at' => ['sometimes', 'required', 'date'],
            'evidence_bundle_ids' => ['sometimes', 'required', 'array'],
            'evidence_bundle_ids.*' => ['integer'],
            'submitted_at' => ['sometimes', 'required', 'date'],
            'submitted_by' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'documents_uri' => ['sometimes', 'required', 'string', 'url', 'max:2048'],
        ];
    }
}
