<?php

namespace App\Http\Requests\CommitteeMeeting;

use Illuminate\Validation\Rule;
use App\Enums\CommitteeMeeting\MeetingType;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeMeeting\AttendancePolicy;

class UpdateCommitteeMeetingRequest extends FormRequest
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
            'ai_committee_id' => ['sometimes', 'exists:ai_committees,id'],
            'meeting_type' => ['sometimes', Rule::enum(MeetingType::class)],
            'scheduled_at' => ['sometimes', 'date'],
            'duration_minutes' => ['sometimes', 'nullable', 'integer'],
            'agenda' => ['sometimes', 'string'],
            'materials_link' => ['sometimes', 'nullable', 'string'],
            'attendance_policy' => ['sometimes', Rule::enum(AttendancePolicy::class)],
            'attendance_roster' => ['sometimes', 'nullable', 'array'],
            'minutes_link' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
