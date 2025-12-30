<?php

namespace App\Http\Requests\DatasetSnapshot;

use Illuminate\Validation\Rule;
use App\Enums\DatasetSnapshot\Status;
use App\Enums\DatasetSnapshot\ApprovedBy;
use App\Enums\DatasetSnapshot\FileFormat;
use App\Enums\DatasetSnapshot\Compression;
use App\Enums\DatasetSnapshot\StorageTier;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DatasetSnapshot\MaskingMethod;
use App\Enums\DatasetSnapshot\ResidencyZone;
use App\Enums\DatasetSnapshot\EncryptionStatus;

class StoreDatasetSnapshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dataset_id' => ['required', 'integer', 'exists:datasets,id'],
            'version_tag' => ['required', 'string', 'max:50'],
            'supersedes_snapshot_id' => ['nullable', 'integer', 'exists:dataset_snapshots,id'],
            'description' => ['nullable', 'string'],
            'time_range_start' => ['required', 'date'],
            'time_range_end' => ['required', 'date', 'after_or_equal:time_range_start'],
            'row_count' => ['required', 'integer', 'min:0'],
            'file_count' => ['nullable', 'integer', 'min:0'],
            'total_size' => ['nullable', 'integer', 'min:0'],
            'size_unit' => ['nullable', 'string', 'max:20'],
            'file_format' => ['required', Rule::enum(FileFormat::class)],
            'pii_element_count' => ['nullable', 'integer', 'min:0'],
            'consent_coverage_at_creation' => ['nullable', 'integer', 'min:0', 'max:100'],
            'residency_zone' => ['required', Rule::enum(ResidencyZone::class)],
            'storage_uri' => ['required', 'string', 'max:500'],
            'storage_tier' => ['nullable', Rule::enum(StorageTier::class)],
            'compression' => ['nullable', Rule::enum(Compression::class)],
            'encryption_status' => ['required', Rule::enum(EncryptionStatus::class)],
            'masking_method_applied' => ['nullable', Rule::enum(MaskingMethod::class)],
            'quality_checksums' => ['nullable', 'string', 'max:255'],
            'created_by_system' => ['nullable', 'boolean'],
            'approved_by' => ['nullable', Rule::enum(ApprovedBy::class)],
            'expiration_date' => ['nullable', 'date', 'after:today'],
            'status' => ['required', Rule::enum(Status::class)],
        ];
    }
}
