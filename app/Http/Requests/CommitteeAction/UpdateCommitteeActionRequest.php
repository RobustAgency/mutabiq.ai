<?php

namespace App\Http\Requests\CommitteeAction;

use Illuminate\Validation\Rule;
use App\Enums\CommitteeAction\Status;
use App\Enums\CommitteeAction\ActionType;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeAction\VerificationResult;

class UpdateCommitteeActionRequest extends FormRequest
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
            'committee_decision_id' => ['sometimes', 'exists:committee_decisions,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'action_type' => ['sometimes', Rule::enum(ActionType::class)],
            'assignee_id' => ['sometimes', 'exists:stakeholders,id'],
            'due_date' => ['sometimes', 'date'],
            'status' => ['sometimes', Rule::enum(Status::class)],
            'verification_result' => ['sometimes', Rule::enum(VerificationResult::class)],
            'evidence_link' => ['sometimes', 'nullable', 'url'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'closed_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
