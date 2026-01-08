<?php

namespace App\Http\Requests\CommitteeMeeting;

use Illuminate\Validation\Rule;
use App\Enums\CommitteeMeeting\MeetingType;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommitteeMeeting\AttendancePolicy;

class StoreCommitteeMeetingRequest extends FormRequest
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
            'ai_committee_id' => ['required', 'exists:ai_committees,id'],
            'meeting_type' => ['required', Rule::enum(MeetingType::class)],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer'],
            'agenda' => ['required', 'string'],
            'materials_link' => ['nullable', 'string'],
            'attendance_policy' => ['required', Rule::enum(AttendancePolicy::class)],
            'attendance_roster' => ['nullable', 'array'],
            'minutes_link' => ['nullable', 'string'],
        ];
    }
}
