<?php

namespace App\Http\Resources;

use App\Models\UserConsent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * @mixin UserConsent
 */
class UserConsentResource extends JsonResource
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
            'subject_key' => $this->subject_key,
            'subject_realm' => $this->subject_realm,
            'jurisdiction' => $this->jurisdiction,
            'consent_purpose' => $this->consent_purpose,
            'consent_status' => $this->consent_status,
            'legal_basis' => $this->legal_basis,
            'source_system' => $this->source_system,
            'effective_from' => $this->effective_from ? Carbon::parse($this->effective_from)->toDateString() : null,
            'effective_to' => $this->effective_to ? Carbon::parse($this->effective_to)->toDateString() : null,
            'scope' => $this->scope,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
