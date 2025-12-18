<?php

namespace App\Http\Requests\ComplianceEvidence;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ComplianceEvidence\ArtifactType;
use App\Enums\ComplianceEvidence\ReviewOutcome;

class StoreComplianceEvidenceRequest extends FormRequest
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
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'control_id' => ['required', 'integer', 'exists:controls,id'],
            'requirement_id' => ['nullable', 'integer', 'exists:requirements,id'],
            'ai_model_id' => ['nullable', 'integer', 'exists:ai_models,id'],
            'artifact_type' => ['required', 'string', Rule::enum(ArtifactType::class)],
            'artifact_uri' => ['required', 'string', 'url', 'max:2048'],
            'sample_ids' => ['required', 'array'],
            'sample_ids.*' => ['string', 'max:255'],
            'sampling_method' => ['required', 'string', 'max:255'],
            'collection_period_start' => ['nullable', 'date'],
            'collection_period_end' => ['nullable', 'date', 'after_or_equal:collection_period_start'],
            'collected_by' => ['nullable', 'integer', 'exists:users,id'],
            'review_outcome' => ['nullable', 'string', Rule::enum(ReviewOutcome::class)],
            'reviewed_by' => ['nullable', 'integer', 'exists:users,id'],
            'reviewed_at' => ['nullable', 'date'],
            'hash_checksum' => ['required', 'string', 'max:255'],
        ];
    }
}
