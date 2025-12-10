<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ConsentRecord
 */
class ConsentRecordResource extends JsonResource
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
            'consent_code' => $this->consent_code,
            'subject_key' => $this->subject_key,
            'subject_realm' => $this->subject_realm,
            'subject_age_group' => $this->subject_age_group,
            'purpose' => $this->purpose,
            'record_of_processing_activity_id' => $this->record_of_processing_activity_id,
            'status' => $this->status,
            'lifecycle_stage' => $this->lifecycle_stage,
            'consent_version' => $this->consent_version,
            'consent_text' => $this->consent_text,
            'consent_method' => $this->consent_method,
            'effective_from' => $this->effective_from->toIso8601String(),
            'effective_to' => $this->effective_to?->toIso8601String(),
            'obtained_date' => $this->obtained_date?->toIso8601String(),
            'withdrawal_date' => $this->withdrawal_date?->toIso8601String(),
            'last_refreshed_date' => $this->last_refreshed_date?->toIso8601String(),
            'source_system' => $this->source_system,
            'evidence_uri' => $this->evidence_uri,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'language' => $this->language,
            'jurisdiction' => $this->jurisdiction,
            'data_categories' => $this->data_categories,
            'can_withdraw' => $this->can_withdraw,
            'withdrawal_method' => $this->withdrawal_method,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
