<?php

namespace App\Http\Requests\CommitteeMembership;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeMembership\MemberRole;
use App\Enums\CommitteeMembership\Eligibility;

class UpdateCommitteeMembershipRequest extends FormRequest
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
     * @return array<string, \Illuminate\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'ai_committee_id' => ['sometimes', 'integer', 'exists:ai_committees,id'],
            'stakeholder_id' => ['sometimes', 'integer', 'exists:stakeholders,id'],
            'member_role' => ['sometimes', Rule::enum(MemberRole::class)],
            'eligibility' => ['sometimes', Rule::enum(Eligibility::class)],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'expertise_tags' => ['sometimes', 'array'],
            'expertise_tags.*' => ['string', 'max:50'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ai_committee_id.exists' => 'The selected committee does not exist.',
            'stakeholder_id.exists' => 'The selected stakeholder does not exist.',
            'member_role.enum' => 'The selected member role is invalid.',
            'eligibility.enum' => 'The selected eligibility status is invalid.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }
}
