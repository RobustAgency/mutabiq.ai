<?php

namespace App\Http\Resources;

use App\Models\DatasetSnapshot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin DatasetSnapshot
 */
class DatasetSnapshotResource extends JsonResource
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
            'version_tag' => $this->version_tag,
            'time_range_start' => $this->time_range_start ? Carbon::parse($this->time_range_start)->toDateTimeString() : null,
            'time_range_end' => $this->time_range_end ? Carbon::parse($this->time_range_end)->toDateTimeString() : null,
            'row_count' => $this->row_count,
            'quality_checksums' => $this->quality_checksums,
            'pii_element_count' => $this->pii_element_count,
            'special_category_element_count' => $this->special_category_element_count,
            'masking_anonymization_method' => $this->masking_anonymization_method,
            'privacy_transform_evidence_ref' => $this->privacy_transform_evidence_ref,
            'residency_zone' => $this->residency_zone,
            'storage_uri' => $this->storage_uri,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'dataset' => new DatasetResource($this->whenLoaded('dataset')),
        ];
    }
}
