<?php

namespace App\Http\Resources;

use App\Models\AiModelVersion;
use Illuminate\Http\Request;
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
            'version' => $this->version_number,
            'version_type' => $this->version_type,
            'version_role' => $this->version_role,
            'version_source' => $this->version_source,
            'our_involvement' => $this->our_involvement,
            'description' => $this->description,
            'release_notes' => $this->release_notes,
            'release_date' => $this->release_date,
            'architecture_type' => $this->architecture_type,
            'model_file_size_gb' => $this->model_file_size_gb,
            'training_duration_hours' => $this->training_duration_hours,
            'complexity' => $this->complexity_level,
            'parameter_count' => $this->parameter_count,
            'input_modalities' => $this->input_modalities,
            'output_modalities' => $this->output_modalities,
            'deployment_status' => $this->deployment_status,
            'lifecycle_stage' => $this->lifecycle_stage,
            'deployment_environments' => $this->deployment_environments,
            'has_performance_data' => $this->has_performance_data,
            'customizations_applied' => $this->customizations_applied,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
