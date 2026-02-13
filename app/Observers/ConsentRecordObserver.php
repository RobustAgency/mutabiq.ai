<?php

namespace App\Observers;

use App\Models\ConsentRecord;

class ConsentRecordObserver extends ActivityAwareObserver
{
    /**
     * Define which fields to track for changes.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [
            'status',
            'lifecycle_stage',
            'consent_method',
            'effective_from',
            'effective_to',
            'withdrawal_date',
            'can_withdraw',
            'withdrawal_method',
            'purpose',
        ];
    }

    /**
     * Handle the ConsentRecord "created" event.
     */
    public function created(ConsentRecord $consentRecord): void
    {
        $this->logCreate($consentRecord);
    }

    /**
     * Handle the ConsentRecord "updating" event.
     */
    public function updating(ConsentRecord $consentRecord): void
    {
        $this->logUpdate($consentRecord, $consentRecord->getOriginal());
    }

    /**
     * Handle the ConsentRecord "deleted" event.
     */
    public function deleted(ConsentRecord $consentRecord): void
    {
        $this->logDelete($consentRecord);
    }
}
