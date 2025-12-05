<?php

namespace App\Http\Resources;

use App\Models\Control;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Control
 */
class ControlResource extends JsonResource
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
            'name' => $this->name,
            'reference' => $this->reference,
            'objective' => $this->objective,
            'testing_method' => $this->testing_method,
            'testing_frequency' => $this->testing_frequency,
            'evidence_expectations' => $this->evidence_expectations,
            'applicability_criteria' => $this->applicability_criteria,
            'status' => $this->status,
            'last_test_date' => $this->last_test_date?->toDateString(),
            'next_test_due' => $this->next_test_due?->toDateString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
