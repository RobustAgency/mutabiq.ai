<?php

namespace App\Http\Requests\CommitteeMembership;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeMembership\MemberRole;
use App\Enums\CommitteeMembership\Eligibility;

class ListCommitteeMembershipRequest extends FormRequest
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
            'ai_committee_id' => ['nullable', 'integer', 'exists:ai_committees,id'],
            'stakeholder_id' => ['nullable', 'integer', 'exists:stakeholders,id'],
            'member_role' => ['nullable', Rule::enum(MemberRole::class)],
            'eligibility' => ['nullable', Rule::enum(Eligibility::class)],
            'active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'active.boolean' => 'The active field must be a boolean.',
            'per_page.min' => 'The per page must be at least 1.',
            'per_page.max' => 'The per page may not be greater than 100.',
        ];
    }
}
