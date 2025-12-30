<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\DatasetSnapshot;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'dataset_id' => $this->dataset_id,
            'version_tag' => $this->version_tag,
            'supersedes_snapshot_id' => $this->supersedes_snapshot_id,
            'description' => $this->description,
            'time_range_start' => $this->time_range_start ? Carbon::parse($this->time_range_start)->toDateTimeString() : null,
            'time_range_end' => $this->time_range_end ? Carbon::parse($this->time_range_end)->toDateTimeString() : null,
            'row_count' => $this->row_count,
            'file_count' => $this->file_count,
            'total_size' => $this->total_size,
            'size_unit' => $this->size_unit,
            'file_format' => $this->file_format,
            'pii_element_count' => $this->pii_element_count,
            'consent_coverage_at_creation' => $this->consent_coverage_at_creation,
            'residency_zone' => $this->residency_zone,
            'storage_uri' => $this->storage_uri,
            'storage_tier' => $this->storage_tier,
            'compression' => $this->compression,
            'encryption_status' => $this->encryption_status,
            'masking_method_applied' => $this->masking_method_applied,
            'quality_checksums' => $this->quality_checksums,
            'created_by_system' => $this->created_by_system,
            'approved_by' => $this->approved_by,
            'expiration_date' => $this->expiration_date ? Carbon::parse($this->expiration_date)->toDateString() : null,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'dataset' => new DatasetResource($this->whenLoaded('dataset')),
        ];
    }
}
