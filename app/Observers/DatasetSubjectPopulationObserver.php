<?php

namespace App\Observers;

use App\Models\DatasetSubjectPopulation;

class DatasetSubjectPopulationObserver extends ActivityAwareObserver
{
    /**
     * Define which fields to track for changes.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [
            'subject_realm',
            'jurisdiction',
            'subjects_total',
            'as_of',
        ];
    }

    /**
     * Handle the DatasetSubjectPopulation "created" event.
     */
    public function created(DatasetSubjectPopulation $population): void
    {
        $this->logCreate($population);
    }

    /**
     * Handle the DatasetSubjectPopulation "updating" event.
     */
    public function updating(DatasetSubjectPopulation $population): void
    {
        $this->logUpdate($population, $population->getOriginal());
    }

    /**
     * Handle the DatasetSubjectPopulation "deleted" event.
     */
    public function deleted(DatasetSubjectPopulation $population): void
    {
        $this->logDelete($population);
    }
}
