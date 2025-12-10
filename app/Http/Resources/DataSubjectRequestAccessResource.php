<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DataSubjectRequestAccess
 */
class DataSubjectRequestAccessResource extends JsonResource
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
            'request_code' => $this->request_code,
            'request_type' => $this->request_type,
            'subject_identifier' => $this->subject_identifier,
            'subject_key' => $this->subject_key,
            'subject_name' => $this->subject_name,
            'subject_realm' => $this->subject_realm,
            'verification_status' => $this->verification_status,
            'verification_method' => $this->verification_method,
            'verification_date' => $this->verification_date?->toIso8601String(),
            'verified_by' => $this->verified_by,
            'request_details' => $this->request_details,
            'requested_data_categories' => $this->requested_data_categories,
            'request_source' => $this->request_source,
            'submitted_date' => $this->submitted_date->toIso8601String(),
            'due_date' => $this->due_date->toIso8601String(),
            'extended_due_date' => $this->extended_due_date?->toIso8601String(),
            'response_date' => $this->response_date?->toIso8601String(),
            'completed_date' => $this->completed_date?->toIso8601String(),
            'status' => $this->status,
            'priority' => $this->priority,
            'is_overdue' => $this->is_overdue,
            'assigned_to' => $this->assigned_to,
            'assigned_date' => $this->assigned_date?->toIso8601String(),
            'response_method' => $this->response_method,
            'response_format' => $this->response_format,
            'response_uri' => $this->response_uri,
            'response_notes' => $this->response_notes,
            'rejection_reason' => $this->rejection_reason,
            'jurisdiction' => $this->jurisdiction,
            'processing_activity_ids' => $this->processing_activity_ids,
            'systems_checked' => $this->systems_checked,
            'records_found' => $this->records_found,
            'remaining_days' => $this->remaining_days,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
