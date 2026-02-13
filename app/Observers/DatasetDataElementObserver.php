<?php

namespace App\Observers;

use App\Models\DatasetDataElement;

class DatasetDataElementObserver extends ActivityAwareObserver
{
    /**
     * Define which fields to track for changes.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [
            'column_name',
            'nullable',
            'sensitivity_override',
            'pii_override',
            'transform_applied',
            'quality_rules_applied',
            'cde_in_dataset',
            'cde_category_in_dataset',
            'lineage_source_column',
            'deprecated',
        ];
    }

    /**
     * Handle the DatasetDataElement "created" event.
     */
    public function created(DatasetDataElement $datasetDataElement): void
    {
        $this->logCreate($datasetDataElement);
    }

    /**
     * Handle the DatasetDataElement "updating" event.
     */
    public function updating(DatasetDataElement $datasetDataElement): void
    {
        $this->logUpdate($datasetDataElement, $datasetDataElement->getOriginal());
    }

    /**
     * Handle the DatasetDataElement "deleted" event.
     */
    public function deleted(DatasetDataElement $datasetDataElement): void
    {
        $this->logDelete($datasetDataElement);
    }
}
