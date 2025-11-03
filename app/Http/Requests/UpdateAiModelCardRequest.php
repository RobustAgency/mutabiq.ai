<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\CreatorRole;
use App\Enums\CardFormat;
use App\Enums\Status;
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
            'version_id' => ['sometimes', 'exists:ai_model_versions,id'],
            'title' => ['sometimes', 'string', 'min:10', 'max:255'],
            'creator_role' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, CreatorRole::cases()))],
            'owner_stakeholder_id' => ['sometimes', 'exists:stakeholders,id'],
            'format' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, CardFormat::cases()))],
            'model_overview' => ['sometimes', 'string'],
            'intended_use' => ['sometimes', 'string'],
            'training_data_overview' => ['sometimes', 'string'],
            'bias_evaluation_methods' => ['sometimes', 'string'],
            'model_limitations' => ['sometimes', 'string'],
            'ethical_considerations' => ['sometimes', 'string'],
            'organizational_context' => ['sometimes', 'array'],
            'performance_summary' => ['sometimes', 'string'],
            'risk_summary' => ['sometimes', 'string'],
            'status' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, Status::cases()))],
            'publication_status' => ['sometimes', Rule::in(array_map(fn($c) => $c->value, PublicationStatus::cases()))],
            'publication_date' => ['sometimes', 'date', 'nullable'],
            'last_review_date' => ['sometimes', 'date', 'nullable'],
            'next_review_date' => ['sometimes', 'date', 'nullable'],
        ];
    }
}
