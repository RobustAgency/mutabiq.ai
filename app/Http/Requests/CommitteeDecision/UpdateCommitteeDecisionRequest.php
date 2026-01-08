<?php

namespace App\Http\Requests\CommitteeDecision;

use Illuminate\Validation\Rule;
use App\Enums\CommitteeDecision\VoteMethod;
use App\Enums\CommitteeDecision\VoteResult;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeDecision\DecisionType;
use App\Enums\CommitteeDecision\DecisionScope;

class UpdateCommitteeDecisionRequest extends FormRequest
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
            'committee_meeting_id' => ['sometimes', 'exists:committee_meetings,id'],
            'decision_type' => ['sometimes', Rule::enum(DecisionType::class)],
            'decision_scope' => ['sometimes', Rule::enum(DecisionScope::class)],
            'ai_model_id' => ['sometimes', 'nullable', 'exists:ai_models,id'],
            'use_case_id' => ['sometimes', 'nullable', 'exists:use_cases,id'],
            'control_id' => ['sometimes', 'nullable', 'exists:controls,id'],
            'related_ref' => ['sometimes', 'nullable', 'string'],
            'rationale' => ['sometimes', 'string'],
            'conditions' => ['sometimes', 'nullable', 'string'],
            'expiry_date' => ['sometimes', 'nullable', 'date'],
            'vote_method' => ['sometimes', Rule::enum(VoteMethod::class)],
            'vote_result' => ['sometimes', Rule::enum(VoteResult::class)],
            'owner_team' => ['sometimes', 'string'],
        ];
    }
}
