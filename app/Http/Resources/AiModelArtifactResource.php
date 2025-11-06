<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AiModelArtifact
 */
class AiModelArtifactResource extends JsonResource
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
            'ai_model_version_id' => $this->ai_model_version_id,
            'artifact_type' => $this->artifact_type,
            'uri' => $this->uri,
            'checksum' => $this->checksum,
            'size_bytes' => $this->size_bytes,
            'created_by' => $this->created_by,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'ai_model_version' => new AiModelVersionResource($this->whenLoaded('aiModelVersion')),
        ];
    }
}
