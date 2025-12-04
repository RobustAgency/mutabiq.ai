<?php

namespace App\Http\Requests\RegulatorySubmission;

use Illuminate\Validation\Rule;
use App\Enums\RegulatorySubmission\Status;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RegulatorySubmission\SubmissionType;

class StoreRegulatorySubmissionRequest extends FormRequest
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
            'framework_id' => ['nullable', 'integer', 'exists:frameworks,id'],
            'ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
            'authority' => ['required', 'string', 'max:255'],
            'jurisdiction' => ['required', 'array', 'min:1'],
            'jurisdiction.*' => ['string', 'max:255'],
            'submission_type' => ['required', 'string', Rule::enum(SubmissionType::class)],
            'content_summary' => ['required', 'string', 'max:5000'],
            'tracking_id' => ['required', 'string', 'max:255', 'unique:regulatory_submissions'],
            'commitments' => ['required', 'array'],
            'commitments.*' => ['string', 'max:1000'],
            'status' => ['required', 'string', Rule::enum(Status::class)],
            'renewal_due_at' => ['required', 'date'],
            'evidence_bundle_ids' => ['required', 'array'],
            'evidence_bundle_ids.*' => ['integer'],
            'submitted_at' => ['required', 'date'],
            'submitted_by' => ['required', 'integer', 'exists:users,id'],
            'documents_uri' => ['required', 'string', 'url', 'max:2048'],
        ];
    }
}
