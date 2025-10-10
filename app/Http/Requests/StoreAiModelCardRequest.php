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

class StoreAiModelCardRequest extends FormRequest
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
            'ai_model_id' => ['required', 'exists:ai_models,id'],
            'ai_model_version_id' => ['required', 'exists:ai_model_versions,id'],
            'title' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:255'],
            'creator_role' => ['required', Rule::in(array_map(fn($c) => $c->value, CreatorRole::cases()))],
            'owner_email' => ['required', 'email'],
            'access_level' => ['required', Rule::in(array_map(fn($c) => $c->value, AccessLevel::cases()))],
            'format' => ['required', Rule::in(array_map(fn($c) => $c->value, CardFormat::cases()))],
            'status' => ['required', Rule::in(array_map(fn($c) => $c->value, Status::cases()))],
            'workflow_stage' => ['required', Rule::in(array_map(fn($c) => $c->value, WorkflowStage::cases()))],
            'technical_review_status' => ['required', Rule::in(array_map(fn($c) => $c->value, TechnicalReviewStatus::cases()))],
            'ethics_review_status' => ['required', Rule::in(array_map(fn($c) => $c->value, EthicsReviewStatus::cases()))],
            'compliance_review_status' => ['required', Rule::in(array_map(fn($c) => $c->value, ComplianceReviewStatus::cases()))],
            'publication_status' => ['required', Rule::in(array_map(fn($c) => $c->value, PublicationStatus::cases()))],
            'completeness_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'organizational_context' => ['nullable', 'string'],
            'intended_use' => ['nullable', 'string'],
            'training_data_overview' => ['nullable', 'string'],
            'bias_evaluation_methods' => ['nullable', 'string'],
            'model_limitations' => ['nullable', 'string'],
            'ethical_considerations' => ['nullable', 'string'],
            'risk_summary' => ['nullable', 'string'],
            'performance_summary' => ['nullable', 'string'],
            'latest_performance_date' => ['nullable', 'date'],
            'publication_date' => ['nullable', 'date'],
            'last_review_date' => ['nullable', 'date'],
            'next_review_date' => ['nullable', 'date'],
        ];
    }
}
