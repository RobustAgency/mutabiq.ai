<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AiModelUseCase
 */
class AiModelUseCaseResource extends JsonResource
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
            'relationship_type' => $this->relationship_type,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'ai_model' => new AiModelResource($this->whenLoaded('aiModel')),
            'use_case' => new UseCaseResource($this->whenLoaded('useCase')),
            'ai_model_version' => new AiModelVersionResource($this->whenLoaded('aiModelVersion')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'updated_by' => new UserResource($this->whenLoaded('updatedBy')),
        ];
    }
}
