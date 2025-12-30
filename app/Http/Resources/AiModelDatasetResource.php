<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\AiModelDataset;
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
            'display_id' => $this->display_id,
            'organization_id' => $this->organization_id,
            'ai_model_id' => $this->ai_model_id,
            'ai_model_version_id' => $this->ai_model_version_id,
            'dataset_id' => $this->dataset_id,
            'dataset_snapshot_id' => $this->dataset_snapshot_id,
            'role' => $this->role,
            'rows_used' => $this->rows_used,
            'training_start_date' => $this->training_start_date,
            'training_end_date' => $this->training_end_date,
            'training_duration' => $this->training_duration,
            'compute_resources' => $this->compute_resources,
            'cost' => $this->cost,
            'consent_check_status' => $this->consent_check_status,
            'cross_border_check' => $this->cross_border_check,
            'special_category_check' => $this->special_category_check,
            'bias_mitigation_applied' => $this->bias_mitigation_applied,
            'created_by_system' => $this->created_by_system,
            'linkage_status' => $this->linkage_status,
            'business_justification' => $this->business_justification,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'ai_model' => new AiModelResource($this->whenLoaded('aiModel')),
            'ai_model_version' => new AiModelVersionResource($this->whenLoaded('aiModelVersion')),
            'dataset' => new DatasetResource($this->whenLoaded('dataset')),
        ];
    }
}
