<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CorrectivePreventiveAction
 */
class CorrectivePreventiveActionResource extends JsonResource
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
            'source_type' => $this->source_type,
            'source_reference' => $this->source_reference,
            'ai_model_id' => $this->ai_model_id,
            'dataset_id' => $this->dataset_id,
            'title' => $this->title,
            'capa_type' => $this->capa_type,
            'priority' => $this->priority,
            'root_cause' => $this->root_cause,
            'actions' => $this->actions,
            'owner_team' => $this->owner_team,
            'assignee' => $this->assignee,
            'due_date' => $this->due_date ? Carbon::parse($this->due_date)->format('Y-m-d') : null,
            'status' => $this->status,
            'success_criteria' => $this->success_criteria,
            'linked_training' => $this->linked_training,
            'estimated_cost' => $this->estimated_cost,
            'effectiveness_review_date' => $this->effectiveness_review_date ? Carbon::parse($this->effectiveness_review_date)->format('Y-m-d') : null,
            'verification_result' => $this->verification_result,
            'evidence_link' => $this->evidence_link,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'ai_model' => $this->whenLoaded('aiModel'),
        ];
    }
}
