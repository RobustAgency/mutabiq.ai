<?php

namespace App\Http\Requests\AiRiskTreatment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiRiskTreatmentRequest extends FormRequest
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
            'ai_risk_register_id' => ['required', 'integer', 'exists:ai_risk_registers,id'],
            'treatment_type' => ['required', 'string', 'max:255'],
            'plan_summary' => ['required', 'string', 'max:255'],
            'owner_stakeholder_id' => ['required', 'integer', 'exists:stakeholders,id'],
            'assignee' => ['nullable', 'array'],
            'assignee.*' => ['string', 'max:255'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:255'],
            'expected_residual_level' => ['nullable', 'string', 'max:255'],
            'result_verification' => ['nullable', 'string', 'max:255'],
            'evidence_link' => ['nullable', 'string', 'max:255'],
            'linked_capa_id' => ['nullable', 'string', 'max:255'],
            'closed_at' => ['nullable', 'date'],
        ];
    }
}
