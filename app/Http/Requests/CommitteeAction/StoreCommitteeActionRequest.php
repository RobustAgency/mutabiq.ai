<?php

namespace App\Http\Requests\CommitteeAction;

use Illuminate\Validation\Rule;
use App\Enums\CommitteeAction\Status;
use App\Enums\CommitteeAction\ActionType;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeAction\VerificationResult;

class StoreCommitteeActionRequest extends FormRequest
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
            'committee_decision_id' => ['required', 'exists:committee_decisions,id'],
            'title' => ['required', 'string', 'max:255'],
            'action_type' => ['required', Rule::enum(ActionType::class)],
            'assignee_id' => ['required', 'exists:stakeholders,id'],
            'due_date' => ['required', 'date', 'after:today'],
            'status' => ['required', Rule::enum(Status::class)],
            'verification_result' => ['required', Rule::enum(VerificationResult::class)],
            'evidence_link' => ['nullable', 'url'],
            'notes' => ['nullable', 'string'],
            'closed_at' => ['nullable', 'date'],
        ];
    }
}
