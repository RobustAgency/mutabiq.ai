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
            'template' => $this->template,
            'language' => $this->language,
            'audience_type' => $this->audience_type,
            'channel' => $this->channel,
            'regulatory_basis' => $this->regulatory_basis,
            'notice_summary' => $this->notice_summary,
            'notice_link' => $this->notice_link,
            'sent_at' => $this->sent_at ? Carbon::parse($this->sent_at)->toIso8601String() : null,
            'notification_deadline' => $this->notification_deadline ? Carbon::parse($this->notification_deadline)->toIso8601String() : null,
            'sent_by' => $this->sent_by,
            'delivery_status' => $this->delivery_status,
            'response_summary' => $this->response_summary,
            'follow_up_required' => $this->follow_up_required,
            'follow_up_date' => $this->follow_up_date ? Carbon::parse($this->follow_up_date)->toIso8601String() : null,
            'follow_up_notes' => $this->follow_up_notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'ai_incident' => new AiIncidentResource($this->whenLoaded('aiIncident')),
        ];
    }
}
