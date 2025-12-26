<?php

namespace App\Http\Requests\Agreement;

use Illuminate\Validation\Rule;
use App\Enums\Agreement\AuditRights;
use App\Enums\Agreement\RenewalType;
use App\Enums\Agreement\GoverningLaw;
use App\Enums\Agreement\AgreementType;
use App\Enums\Agreement\TrainingOptOut;
use App\Enums\Agreement\AgreementStatus;
use App\Enums\Agreement\Indemnification;
use App\Enums\Agreement\ParentAgreement;
use App\Enums\Agreement\DisputeResolution;
use App\Enums\Agreement\ReplacesAgreement;
use App\Enums\Agreement\TransferMechanism;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Agreement\ConfidentialityTerm;
use App\Enums\Agreement\SubProcessingRights;

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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vendor_id' => ['sometimes', 'integer', 'exists:vendors,id'],

            'agreement_type' => ['sometimes', Rule::enum(AgreementType::class)],
            'status' => ['sometimes', Rule::enum(AgreementStatus::class)],

            'agreement_owner_id' => ['sometimes', 'integer', 'exists:stakeholders,id'],

            'asset_types_covered' => ['sometimes', 'array', 'min:1'],
            'asset_types_covered.*' => ['string'],

            'renewal_type' => ['nullable', Rule::enum(RenewalType::class)],
            'notice_period_days' => ['nullable', 'integer', 'min:0'],

            'termination_for_convenience' => ['nullable', 'boolean'],

            'governing_law' => ['nullable', Rule::enum(GoverningLaw::class)],

            'effective_from' => ['sometimes', 'date'],
            'effective_to' => ['sometimes', 'date', 'after:effective_from'],

            'training_opt_out' => ['nullable', Rule::enum(TrainingOptOut::class)],
            'audit_rights' => ['nullable', Rule::enum(AuditRights::class)],
            'transfer_mechanism' => ['nullable', Rule::enum(TransferMechanism::class)],
            'sub_processing_rights' => ['nullable', Rule::enum(SubProcessingRights::class)],

            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'liability_cap' => ['nullable', 'numeric', 'min:0'],
            'insurance_requirements' => ['nullable', 'string'],

            'indemnification' => ['nullable', Rule::enum(Indemnification::class)],

            'internal_reference_number' => ['nullable', 'string', 'max:255'],
            'vendor_contract_id' => ['nullable', 'string', 'max:255'],

            'dispute_resolution' => ['nullable', Rule::enum(DisputeResolution::class)],
            'confidentiality_term' => ['nullable', Rule::enum(ConfidentialityTerm::class)],

            'parent_agreement' => ['nullable', Rule::enum(ParentAgreement::class)],
            'replaces_agreement' => ['nullable', Rule::enum(ReplacesAgreement::class)],

            'notes' => ['nullable', 'string'],

            'doc_ref' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
