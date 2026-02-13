<?php

namespace App\Observers;

use App\Models\DataSource;

class DataSourceObserver extends ActivityAwareObserver
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
            'system_type',
            'owner_team',
            'data_domains',
            'residency',
            'criticality_level',
            'hosting_model',
            'technical_owner',
            'business_owner',
            'last_review_date',
            'next_review_date',
            'status',
        ];
    }

    /**
     * Handle the DataSource "created" event.
     */
    public function created(DataSource $dataSource): void
    {
        $this->logCreate($dataSource);
    }

    /**
     * Handle the DataSource "updating" event.
     */
    public function updating(DataSource $dataSource): void
    {
        $this->logUpdate($dataSource, $dataSource->getOriginal());
    }

    /**
     * Handle the DataSource "deleted" event.
     */
    public function deleted(DataSource $dataSource): void
    {
        $this->logDelete($dataSource);
    }
}
