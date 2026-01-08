<?php

namespace App\Http\Requests\CommitteeMeeting;

use Illuminate\Validation\Rule;
use App\Enums\CommitteeMeeting\MeetingType;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeMeeting\AttendancePolicy;

class ListCommitteeMeetingRequest extends FormRequest
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
            'ai_committee_id' => ['nullable', 'exists:ai_committees,id'],
            'meeting_type' => ['nullable', Rule::enum(MeetingType::class)],
            'attendance_policy' => ['nullable', Rule::enum(AttendancePolicy::class)],
            'scheduled_after' => ['nullable', 'date'],
            'scheduled_before' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
