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
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'ai_model_id' => $this->ai_model_id,
            'title' => $this->title,
            'capa_type' => $this->capa_type,
            'priority' => $this->priority,
            'owner_team' => $this->owner_team,
            'assignee' => $this->assignee,
            'root_cause' => $this->root_cause,
            'actions' => $this->actions,
            'due_date' => $this->due_date ? Carbon::parse($this->due_date)->format('Y-m-d') : null,
            'status' => $this->status,
            'verification_result' => $this->verification_result,
            'evidence_link' => $this->evidence_link,
            'closed_at' => $this->closed_at ? Carbon::parse($this->closed_at)->toIso8601String() : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'ai_model' => $this->whenLoaded('aiModel')
        ];
    }
}
