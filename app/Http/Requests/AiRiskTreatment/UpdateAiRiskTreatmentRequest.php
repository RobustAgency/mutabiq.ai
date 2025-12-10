<?php

namespace App\Http\Requests\AiRiskTreatment;

use Illuminate\Validation\Rule;
use App\Enums\AiRiskTreatment\Status;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AiRiskTreatment\TreatmentType;
use App\Enums\AiRiskTreatment\ResultVerification;

class UpdateAiRiskTreatmentRequest extends FormRequest
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
            'ai_risk_register_id' => ['sometimes', 'required', 'integer', 'exists:ai_risk_registers,id'],
            'treatment_type' => ['sometimes', 'required', Rule::in(array_map(fn ($c) => $c->value, TreatmentType::cases()))],
            'plan_summary' => ['sometimes', 'required', 'string', 'max:255'],
            'owner_stakeholder_id' => ['sometimes', 'required', 'integer', 'exists:stakeholders,id'],
            'assignee' => ['nullable', 'array'],
            'assignee.*' => ['string', 'max:255'],
            'due_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', Rule::in(array_map(fn ($c) => $c->value, Status::cases()))],
            'expected_residual_level' => ['nullable', 'string', 'max:255'],
            'result_verification' => ['nullable', Rule::in(array_map(fn ($c) => $c->value, ResultVerification::cases()))],
            'evidence_link' => ['nullable', 'string', 'max:255'],
            'linked_capa_id' => ['nullable', 'string', 'max:255'],
            'closed_at' => ['nullable', 'date'],
        ];
    }
}
