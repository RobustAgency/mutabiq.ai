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
            'version' => $this->version_number,
            'version_type' => $this->version_type,
            'description' => $this->description,
            'release_notes' => $this->release_notes,
            'release_date' => $this->release_date,
            'architecture_type' => $this->architecture_type,
            'model_file_size_gb' => $this->model_file_size_gb,
            'training_duration_hours' => $this->training_duration_hours,
            'complexity' => $this->complexity_level,
            'deployment_status' => $this->deployment_status,
            'lifecycle_stage' => $this->lifecycle_stage,
            'validation_status' => $this->validation_status,
            'compliance_status' => $this->compliance_check_status,
            'parameter_count' => $this->parameter_count,
            'input_modalities' => $this->input_modalities,
            'output_modalities' => $this->output_modalities,
            'deployment_environments' => $this->deployment_environments,
            'rollback_available' => $this->rollback_available,
            'has_performance_data' => $this->has_performance_data,
            'performance_baseline_established' => $this->performance_baseline_established,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
