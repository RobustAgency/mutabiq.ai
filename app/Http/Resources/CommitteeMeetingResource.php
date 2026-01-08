<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CommitteeMeeting
 */
class CommitteeMeetingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ai_committee_id' => $this->ai_committee_id,
            'meeting_type' => $this->meeting_type,
            'scheduled_at' => $this->scheduled_at,
            'duration_minutes' => $this->duration_minutes,
            'agenda' => $this->agenda,
            'materials_link' => $this->materials_link,
            'attendance_policy' => $this->attendance_policy,
            'attendance_roster' => $this->attendance_roster,
            'minutes_link' => $this->minutes_link,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'committee' => new AiCommitteeResource($this->whenLoaded('committee')),
        ];
    }
}
