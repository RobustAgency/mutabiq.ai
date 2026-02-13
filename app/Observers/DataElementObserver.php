<?php

namespace App\Observers;

use App\Models\DataElement;

class DataElementObserver extends ActivityAwareObserver
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
            'business_definition',
            'data_steward',
            'status',
            'database_name',
            'schema_name',
            'table_name',
            'column_name',
            'data_type',
            'format',
            'sensitivity',
            'contains_personal_data',
            'personal_data_type',
            'contains_sensitive_data',
            'default_masking_method',
            'cde_flag',
            'cde_categories',
        ];
    }

    /**
     * Handle the DataElement "created" event.
     */
    public function created(DataElement $dataElement): void
    {
        $this->logCreate($dataElement);
    }

    /**
     * Handle the DataElement "updating" event.
     */
    public function updating(DataElement $dataElement): void
    {
        $this->logUpdate($dataElement, $dataElement->getOriginal());
    }

    /**
     * Handle the DataElement "deleted" event.
     */
    public function deleted(DataElement $dataElement): void
    {
        $this->logDelete($dataElement);
    }
}
