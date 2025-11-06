<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ArtifactAccessLog
 */
class ArtifactAccessLogResource extends JsonResource
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
            'artifact_id' => $this->artifact_id,
            'accessor_stakeholder_id' => $this->accessor_stakeholder_id,
            'action' => $this->action,
            'context' => $this->context,
            'ts' => $this->ts,
            'ip_or_agent' => $this->ip_or_agent,
            'request_id' => $this->request_id,
            'reason' => $this->reason,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'artifact' => new AiModelArtifactResource($this->whenLoaded('artifact')),
        ];
    }
}
