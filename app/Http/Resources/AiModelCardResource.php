<?php

namespace App\Http\Resources;

use App\Models\AiModelCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiModelCard
 */
class AiModelCardResource extends JsonResource
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
            'title' => $this->title,
            'version' => $this->version,
            'creator_role' => $this->creator_role,
            'access_level' => $this->access_level,
            'owner_email' => $this->owner_email,
            'format' => $this->format,
            'status' => $this->status,
            'workflow_stage' => $this->workflow_stage,
            'technical_review_status' => $this->technical_review_status,
            'ethics_review_status' => $this->ethics_review_status,
            'compliance_review_status' => $this->compliance_review_status,
            'publication_status' => $this->publication_status,
            'completeness_score' => $this->completeness_score,
            'organizational_context' => $this->organizational_context,
            'intended_use' => $this->intended_use,
            'training_data_overview' => $this->training_data_overview,
            'bias_evaluation_methods' => $this->bias_evaluation_methods,
            'model_limitations' => $this->model_limitations,
            'ethical_considerations' => $this->ethical_considerations,
            'risk_summary' => $this->risk_summary,
            'performance_summary' => $this->performance_summary,
            'latest_performance_date' => $this->latest_performance_date,
            'publication_date' => $this->publication_date,
            'last_review_date' => $this->last_review_date,
            'next_review_date' => $this->next_review_date,
            'ai_model' => new AiModelResource($this->whenLoaded('aiModel')),
            'ai_model_version' => new AiModelVersionResource($this->whenLoaded('aiModelVersion')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
