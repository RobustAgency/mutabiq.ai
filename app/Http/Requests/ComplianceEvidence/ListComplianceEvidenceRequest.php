<?php

namespace App\Http\Requests\ComplianceEvidence;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ComplianceEvidence\ArtifactType;
use App\Enums\ComplianceEvidence\ReviewOutcome;

class ListComplianceEvidenceRequest extends FormRequest
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
            'artifact_type' => ['nullable', 'string', Rule::enum(ArtifactType::class)],
            'review_outcome' => ['nullable', 'string', Rule::enum(ReviewOutcome::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
