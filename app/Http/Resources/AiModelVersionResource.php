<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\AiModelVersion;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiModelVersion
 */
class AiModelVersionResource extends JsonResource
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
            'organization_id' => $this->organization_id,
            'ai_model_id' => $this->ai_model_id,
            'version_number' => $this->version_number,
            'version_type' => $this->version_type,
            'release_role' => $this->release_role,
            'source_type' => $this->source_type,
            'approval_status' => $this->approval_status,
            'org_involvement' => $this->org_involvement,
            'description' => $this->description,
            'release_date' => $this->release_date,
            'release_notes' => $this->release_notes,
            'architecture_type' => $this->architecture_type,
            'model_file_size_gb' => $this->model_file_size_gb,
            'training_duration_hours' => $this->training_duration_hours,
            'complexity_level' => $this->complexity_level,
            'parameter_count' => $this->parameter_count,
            'input_modalities' => $this->input_modalities,
            'output_modalities' => $this->output_modalities,
            'deployment_status' => $this->deployment_status,
            'lifecycle_stage' => $this->lifecycle_stage,
            'deployment_environments' => $this->deployment_environments,
            'customizations_applied' => $this->customizations_applied,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'ai_model' => $this->whenLoaded('aiModel'),
        ];
    }
}
