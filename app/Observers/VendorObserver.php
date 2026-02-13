<?php

namespace App\Observers;

use App\Models\Vendor;

class VendorObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'vendor_name',
            'legal_name',
            'hq_country',
            'risk_tier',
            'status',
            'type',
            'data_processing_role',
            'service_provided',
            'primary_contacts',
            'metadata',
            'duns_number',
            'lei_number',
            'tax_id',
            'stock_ticker',
            'notes',
        ];
    }

    public function created(Vendor $vendor): void
    {
        $this->logCreate($vendor);
    }

    public function updating(Vendor $vendor): void
    {
        $this->logUpdate($vendor, $vendor->getOriginal());
    }

    public function deleted(Vendor $vendor): void
    {
        $this->logDelete($vendor);
    }
}
