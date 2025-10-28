<?php

namespace App\Http\Requests\Agreement;

use App\Enums\Agreement\AgreementType;
use App\Enums\Agreement\TrainingOptOut;
use App\Enums\Agreement\AuditRights;
use App\Enums\Agreement\TransferMechanism;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgreementRequest extends FormRequest
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
            'vendor_id' => ['sometimes', 'integer', 'exists:vendors,id'],
            'agreement_type' => ['sometimes', 'string', Rule::in(array_map(fn($type) => $type, AgreementType::cases()))],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'active', 'lapsed', 'terminated'])],
            'effective_from' => ['sometimes', 'date'],
            'effective_to' => ['sometimes', 'date', 'after:effective_from'],
            'training_opt_out' => ['nullable', 'string', Rule::in(array_map(fn($option) => $option, TrainingOptOut::cases()))],
            'audit_rights' => ['nullable', 'string', Rule::in(array_map(fn($option) => $option, AuditRights::cases()))],
            'transfer_mechanism' => ['nullable', 'string', Rule::in(array_map(fn($option) => $option, TransferMechanism::cases()))],
            'sla_terms' => ['nullable', 'array'],
            'sla_terms.availability_target_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sla_terms.latency_p95_ms' => ['nullable', 'integer', 'min:0'],
            'sla_terms.support_tier' => ['nullable', 'string'],
            'sla_terms.breach_definition' => ['nullable', 'string'],
            'sla_terms.credit_schedule_ref' => ['nullable', 'string'],
            'sla_terms.monitoring_ref' => ['nullable', 'string'],
            'doc_ref' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
