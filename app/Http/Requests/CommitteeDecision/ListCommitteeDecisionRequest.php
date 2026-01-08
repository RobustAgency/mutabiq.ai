<?php

namespace App\Http\Requests\CommitteeDecision;

use Illuminate\Validation\Rule;
use App\Enums\CommitteeDecision\VoteMethod;
use App\Enums\CommitteeDecision\VoteResult;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeDecision\DecisionType;
use App\Enums\CommitteeDecision\DecisionScope;

class ListCommitteeDecisionRequest extends FormRequest
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
            'committee_meeting_id' => ['nullable', 'exists:committee_meetings,id'],
            'decision_type' => ['nullable', Rule::enum(DecisionType::class)],
            'decision_scope' => ['nullable', Rule::enum(DecisionScope::class)],
            'vote_method' => ['nullable', Rule::enum(VoteMethod::class)],
            'vote_result' => ['nullable', Rule::enum(VoteResult::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
