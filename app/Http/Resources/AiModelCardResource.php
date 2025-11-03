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
            'version_id' => $this->version_id,
            'title' => $this->title,
            'creator_role' => $this->creator_role,
            'owner_stakeholder_id' => $this->owner_stakeholder_id,
            'format' => $this->format,
            'model_overview' => $this->model_overview,
            'intended_use' => $this->intended_use,
            'training_data_overview' => $this->training_data_overview,
            'bias_evaluation_methods' => $this->bias_evaluation_methods,
            'model_limitations' => $this->model_limitations,
            'ethical_considerations' => $this->ethical_considerations,
            'organizational_context' => $this->organizational_context,
            'performance_summary' => $this->performance_summary,
            'risk_summary' => $this->risk_summary,
            'status' => $this->status,
            'publication_status' => $this->publication_status,
            'publication_date' => $this->publication_date?->toDateString(),
            'last_review_date' => $this->last_review_date?->toDateString(),
            'next_review_date' => $this->next_review_date?->toDateString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];
    }
}
