<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DatasetSubjectPopulation
 */
class DatasetSubjectPopulationResource extends JsonResource
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
            'dataset_id' => $this->dataset_id,
            'snapshot_id' => $this->snapshot_id,
            'subject_realm' => $this->subject_realm,
            'jurisdiction' => $this->jurisdiction,
            'subjects_total' => $this->subjects_total,
            'as_of' => Carbon::parse($this->as_of)->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
