<?php

namespace App\Http\Requests\CommitteeAction;

use Illuminate\Validation\Rule;
use App\Enums\CommitteeAction\Status;
use App\Enums\CommitteeAction\ActionType;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeAction\VerificationResult;

class ListCommitteeActionRequest extends FormRequest
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
            'committee_decision_id' => ['nullable', 'exists:committee_decisions,id'],
            'action_type' => ['nullable', Rule::enum(ActionType::class)],
            'assignee_id' => ['nullable', 'exists:stakeholders,id'],
            'status' => ['nullable', Rule::enum(Status::class)],
            'verification_result' => ['nullable', Rule::enum(VerificationResult::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
