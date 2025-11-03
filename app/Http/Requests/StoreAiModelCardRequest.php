<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\CreatorRole;
use App\Enums\CardFormat;
use App\Enums\Status;
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
            'version_id' => ['required', 'exists:ai_model_versions,id'],
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'creator_role' => ['required', Rule::in(array_map(fn($c) => $c->value, CreatorRole::cases()))],
            'owner_stakeholder_id' => ['required', 'exists:stakeholders,id'],
            'format' => ['required', Rule::in(array_map(fn($c) => $c->value, CardFormat::cases()))],
            'model_overview' => ['required', 'string'],
            'intended_use' => ['required', 'string'],
            'training_data_overview' => ['required', 'string'],
            'bias_evaluation_methods' => ['required', 'string'],
            'model_limitations' => ['required', 'string'],
            'ethical_considerations' => ['required', 'string'],
            'organizational_context' => ['nullable', 'array'],
            'performance_summary' => ['required', 'string'],
            'risk_summary' => ['required', 'string'],
            'status' => ['required', Rule::in(array_map(fn($c) => $c->value, Status::cases()))],
            'publication_status' => ['required', Rule::in(array_map(fn($c) => $c->value, PublicationStatus::cases()))],
            'publication_date' => ['nullable', 'date'],
            'last_review_date' => ['nullable', 'date'],
            'next_review_date' => ['nullable', 'date'],
        ];
    }
}
