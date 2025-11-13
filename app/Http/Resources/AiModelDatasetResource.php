<?php

namespace App\Http\Resources;

use App\Models\AiModelDataset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiModelDataset
 */
class AiModelDatasetResource extends JsonResource
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
            'ai_model_id' => $this->ai_model_id,
            'ai_model_version_id' => $this->ai_model_version_id,
            'dataset_id' => $this->dataset_id,
            'dataset_snapshot_id' => $this->dataset_snapshot_id,
            'role' => $this->role,
            'access_path' => $this->access_path,
            'transform_pack_link' => $this->transform_pack_link,
            'license_check_ref' => $this->license_check_ref,
            'privacy_check_ref' => $this->privacy_check_ref,
            'eligibility_status' => $this->eligibility_status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'ai_model' => new AiModelResource($this->whenLoaded('aiModel')),
            'ai_model_version' => new AiModelVersionResource($this->whenLoaded('aiModelVersion')),
            'dataset' => new DatasetResource($this->whenLoaded('dataset')),
        ];
    }
}
