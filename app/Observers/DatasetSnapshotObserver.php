<?php

namespace App\Observers;

use App\Models\DatasetSnapshot;

class DatasetSnapshotObserver extends ActivityAwareObserver
{
    /**
     * Define which fields to track for changes.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [
            'version_tag',
            'description',
            'time_range_start',
            'time_range_end',
            'row_count',
            'pii_element_count',
            'consent_coverage_at_creation',
            'file_count',
            'total_size',
            'storage_uri',
            'storage_tier',
            'compression',
            'encryption_status',
            'masking_method_applied',
            'approved_by',
            'expiration_date',
            'status',
        ];
    }

    /**
     * Handle the DatasetSnapshot "created" event.
     */
    public function created(DatasetSnapshot $snapshot): void
    {
        $this->logCreate($snapshot);
    }

    /**
     * Handle the DatasetSnapshot "updating" event.
     */
    public function updating(DatasetSnapshot $snapshot): void
    {
        $this->logUpdate($snapshot, $snapshot->getOriginal());
    }

    /**
     * Handle the DatasetSnapshot "deleted" event.
     */
    public function deleted(DatasetSnapshot $snapshot): void
    {
        $this->logDelete($snapshot);
    }
}
