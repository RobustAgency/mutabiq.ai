<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin \App\Models\ConsentCoverage
 */
class ConsentCoverageResource extends JsonResource
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
            'purpose' => $this->purpose,
            'jurisdiction' => $this->jurisdiction,
            'as_of' => $this->as_of ? Carbon::parse($this->as_of)->toDateTimeString() : null,
            'subjects_total' => $this->subjects_total,
            'subjects_with_valid_consent' => $this->subjects_with_valid_consent,
            'coverage_pct' => (float) $this->coverage_pct,
            'evidence_ref' => $this->evidence_ref,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'dataset' => new DatasetResource($this->whenLoaded('dataset')),
            'snapshot' => new DatasetSnapshotResource($this->whenLoaded('snapshot')),
        ];
    }
}
