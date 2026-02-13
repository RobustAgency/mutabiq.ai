<?php

namespace App\Observers;

use App\Models\Dataset;

class DatasetObserver extends ActivityAwareObserver
{
    /**
     * Define which fields to track for changes.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [
            'name',
            'description',
            'purpose',
            'owner_team',
            'data_steward',
            'status',
            'estimated_row_count',
            'estimated_size',
            'size_unit',
            'retention_period',
            'contains_personal_data',
            'sensitivity',
            'cross_border_transfer',
            'license_type',
        ];
    }

    /**
     * Handle the Dataset "created" event.
     */
    public function created(Dataset $dataset): void
    {
        $this->logCreate($dataset);
    }

    /**
     * Handle the Dataset "updating" event.
     */
    public function updating(Dataset $dataset): void
    {
        $this->logUpdate($dataset, $dataset->getOriginal());
    }

    /**
     * Handle the Dataset "deleted" event.
     */
    public function deleted(Dataset $dataset): void
    {
        $this->logDelete($dataset);
    }
}
