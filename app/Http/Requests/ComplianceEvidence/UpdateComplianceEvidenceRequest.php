<?php

namespace App\Http\Requests\ComplianceEvidence;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ComplianceEvidence\ArtifactType;
use App\Enums\ComplianceEvidence\ReviewOutcome;

class UpdateComplianceEvidenceRequest extends FormRequest
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
            'control_id' => ['sometimes', 'required', 'integer', 'exists:controls,id'],
            'requirement_id' => ['sometimes', 'nullable', 'integer', 'exists:requirements,id'],
            'ai_model_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_models,id'],
            'artifact_type' => ['sometimes', 'required', 'string', Rule::enum(ArtifactType::class)],
            'artifact_uri' => ['sometimes', 'required', 'string', 'url', 'max:2048'],
            'sample_ids' => ['sometimes', 'required', 'array'],
            'sample_ids.*' => ['string', 'max:255'],
            'sampling_method' => ['sometimes', 'required', 'string', 'max:255'],
            'collection_period_start' => ['sometimes', 'nullable', 'date'],
            'collection_period_end' => ['sometimes', 'nullable', 'date', 'after_or_equal:collection_period_start'],
            'collected_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'review_outcome' => ['sometimes', 'nullable', 'string', Rule::enum(ReviewOutcome::class)],
            'reviewed_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'reviewed_at' => ['sometimes', 'nullable', 'date'],
            'hash_checksum' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
