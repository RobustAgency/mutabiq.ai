<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CommitteeDecision
 */
class CommitteeDecisionResource extends JsonResource
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
            'committee_meeting_id' => $this->committee_meeting_id,
            'decision_type' => $this->decision_type,
            'decision_scope' => $this->decision_scope,
            'ai_model_id' => $this->ai_model_id,
            'use_case_id' => $this->use_case_id,
            'control_id' => $this->control_id,
            'related_ref' => $this->related_ref,
            'rationale' => $this->rationale,
            'conditions' => $this->conditions,
            'expiry_date' => $this->expiry_date,
            'vote_method' => $this->vote_method,
            'vote_result' => $this->vote_result,
            'owner_team' => $this->owner_team,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'committee_meeting' => new CommitteeMeetingResource($this->whenLoaded('committeeMeeting')),
        ];
    }
}
