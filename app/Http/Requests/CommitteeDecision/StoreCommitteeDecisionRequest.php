<?php

namespace App\Http\Requests\CommitteeDecision;

use Illuminate\Validation\Rule;
use App\Enums\CommitteeDecision\VoteMethod;
use App\Enums\CommitteeDecision\VoteResult;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeDecision\DecisionType;
use App\Enums\CommitteeDecision\DecisionScope;

class StoreCommitteeDecisionRequest extends FormRequest
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
            'committee_meeting_id' => ['required', 'exists:committee_meetings,id'],
            'decision_type' => ['required', Rule::enum(DecisionType::class)],
            'decision_scope' => ['required', Rule::enum(DecisionScope::class)],
            'ai_model_id' => ['nullable', 'exists:ai_models,id'],
            'use_case_id' => ['nullable', 'exists:use_cases,id'],
            'control_id' => ['nullable', 'exists:controls,id'],
            'related_ref' => ['nullable', 'string'],
            'rationale' => ['required', 'string'],
            'conditions' => ['nullable', 'string'],
            'expiry_date' => ['nullable', 'date'],
            'vote_method' => ['required', Rule::enum(VoteMethod::class)],
            'vote_result' => ['required', Rule::enum(VoteResult::class)],
            'owner_team' => ['required', 'string'],
        ];
    }
}
