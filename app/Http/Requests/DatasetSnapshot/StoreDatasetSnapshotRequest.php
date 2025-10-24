<?php

namespace App\Http\Requests\DatasetSnapshot;

use App\Enums\DatasetSnapshot\ResidencyZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'time_range_start' => ['nullable', 'date'],
            'time_range_end' => ['nullable', 'date', 'after_or_equal:time_range_start'],
            'row_count' => ['nullable', 'integer', 'min:0'],
            'quality_checksums' => ['nullable', 'string', 'max:255'],
            'pii_element_count' => ['nullable', 'integer', 'min:0'],
            'special_category_element_count' => ['nullable', 'integer', 'min:0'],
            'masking_anonymization_method' => ['nullable', 'string', 'max:255'],
            'privacy_transform_evidence_ref' => ['nullable', 'string', 'max:255'],
            'residency_zone' => ['required', Rule::in(array_map(fn($zone) => $zone->value, ResidencyZone::cases()))],
            'storage_uri' => ['required', 'string', 'max:500'],
        ];
    }
}
