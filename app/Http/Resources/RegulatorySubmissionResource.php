<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\RegulatorySubmission
 */
class RegulatorySubmissionResource extends JsonResource
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
            'framework_id' => $this->framework_id,
            'ai_model_id' => $this->ai_model_id,
            'authority' => $this->authority,
            'jurisdiction' => $this->jurisdiction,
            'submission_type' => $this->submission_type,
            'content_summary' => $this->content_summary,
            'tracking_id' => $this->tracking_id,
            'status' => $this->status,
            'commitments' => $this->commitments,
            'evidence_bundle_ids' => $this->evidence_bundle_ids,
            'submitted_by' => $this->submitted_by,
            'submitted_at' => $this->submitted_at?->toDateTimeString(),
            'renewal_due_at' => $this->renewal_due_at?->toDateTimeString(),
            'documents_uri' => $this->documents_uri,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'framework' => new FrameworkResource($this->whenLoaded('framework')),
            'ai_model' => new AiModelResource($this->whenLoaded('aiModel')),
            'submitted_by_user' => new UserResource($this->whenLoaded('submittedBy')),
        ];
    }
}
