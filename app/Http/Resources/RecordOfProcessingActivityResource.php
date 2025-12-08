<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\RecordOfProcessingActivity
 */
class RecordOfProcessingActivityResource extends JsonResource
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
            'activity_code' => $this->activity_code,
            'activity_name' => $this->activity_name,
            'purpose' => $this->purpose,
            'detailed_purpose' => $this->detailed_purpose,
            'owner_team' => $this->owner_team,
            'controller_role' => $this->controller_role,
            'data_subject_categories' => $this->data_subject_categories,
            'data_categories' => $this->data_categories,
            'contains_pii' => $this->contains_pii,
            'consent_required' => $this->consent_required,
            'lawful_basis' => $this->lawful_basis,
            'legitimate_interest_assessment' => $this->legitimate_interest_assessment,
            'consent_coverage_percent' => $this->consent_coverage_percent,
            'dpia_required' => $this->dpia_required,
            'dpia_status' => $this->dpia_status,
            'dpia_id' => $this->dpia_id,
            'retention_period' => $this->retention_period,
            'retention_justification' => $this->retention_justification,
            'has_international_transfers' => $this->has_international_transfers,
            'applicable_jurisdictions' => $this->applicable_jurisdictions,
            'security_measures' => $this->security_measures,
            'internal_recipients' => $this->internal_recipients,
            'external_recipients' => $this->external_recipients,
            'status' => $this->status,
            'last_reviewed_date' => $this->last_reviewed_date?->toIso8601String(),
            'next_review_date' => $this->next_review_date?->toIso8601String(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'version' => $this->version,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
