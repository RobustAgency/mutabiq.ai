<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\AccessLevel;
use App\Enums\CreatorRole;
use App\Enums\CardFormat;
use App\Enums\Status;
use App\Enums\WorkflowStage;
use App\Enums\TechnicalReviewStatus;
use App\Enums\EthicsReviewStatus;
use App\Enums\ComplianceReviewStatus;
use App\Enums\PublicationStatus;

class UpdateAiModelCardRequest extends FormRequest
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
            'ai_model_id' => ['sometimes', 'exists:ai_models,id'],
            'ai_model_version_id' => ['sometimes', 'exists:ai_model_versions,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'version' => ['sometimes', 'string', 'max:255'],
            'creator_role' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, CreatorRole::cases()))],
            'owner_email' => ['sometimes', 'email'],
            'access_level' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, AccessLevel::cases()))],
            'format' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, CardFormat::cases()))],
            'status' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, Status::cases()))],
            'workflow_stage' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, WorkflowStage::cases()))],
            'technical_review_status' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, TechnicalReviewStatus::cases()))],
            'ethics_review_status' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, EthicsReviewStatus::cases()))],
            'compliance_review_status' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, ComplianceReviewStatus::cases()))],
            'publication_status' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, PublicationStatus::cases()))],
            'completeness_score' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'organizational_context' => ['sometimes', 'string'],
            'intended_use' => ['sometimes', 'string'],
            'training_data_overview' => ['sometimes', 'string'],
            'bias_evaluation_methods' => ['sometimes', 'string'],
            'model_limitations' => ['sometimes', 'string'],
            'ethical_considerations' => ['sometimes', 'string'],
            'risk_summary' => ['sometimes', 'string'],
            'performance_summary' => ['sometimes', 'string'],
            'latest_performance_date' => ['sometimes', 'date'],
            'publication_date' => ['sometimes', 'date'],
            'last_review_date' => ['sometimes', 'date'],
            'next_review_date' => ['sometimes', 'date'],
        ];
    }
}
