<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CommitteeAction
 */
class CommitteeActionResource extends JsonResource
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
            'committee_decision_id' => $this->committee_decision_id,
            'title' => $this->title,
            'action_type' => $this->action_type,
            'assignee_id' => $this->assignee_id,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'verification_result' => $this->verification_result,
            'evidence_link' => $this->evidence_link,
            'notes' => $this->notes,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'committee_decision' => new CommitteeDecisionResource($this->whenLoaded('committeeDecision')),
            'assignee' => new StakeholderResource($this->whenLoaded('assignee')),
        ];
    }
}
