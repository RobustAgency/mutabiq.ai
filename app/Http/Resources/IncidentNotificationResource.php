<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\IncidentNotification
 */
class IncidentNotificationResource extends JsonResource
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
            'ai_incident_id' => $this->ai_incident_id,
            'audience_type' => $this->audience_type,
            'channel' => $this->channel,
            'notice_summary' => $this->notice_summary,
            'notice_link' => $this->notice_link,
            'notified_at' => $this->notified_at ? Carbon::parse($this->notified_at)->toIso8601String() : null,
            'approved_by' => $this->approved_by,
            'approval_ref' => $this->approval_ref,
            'follow_up_required' => $this->follow_up_required,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'ai_incident' => new AiIncidentResource($this->whenLoaded('aiIncident')),
        ];
    }
}
