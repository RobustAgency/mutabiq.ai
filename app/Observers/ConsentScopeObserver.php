<?php

namespace App\Observers;

use App\Models\ConsentScope;

class ConsentScopeObserver extends ActivityAwareObserver
{
    /**
     * Define which fields to track for changes.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [
            'dataset_id',
            'purpose',
            'subject_realm',
            'jurisdiction',
            'effective_from',
            'effective_to',
            'source_created_at',
        ];
    }

    /**
     * Handle the ConsentScope "created" event.
     */
    public function created(ConsentScope $consentScope): void
    {
        $this->logCreate($consentScope);
    }

    /**
     * Handle the ConsentScope "updating" event.
     */
    public function updating(ConsentScope $consentScope): void
    {
        $this->logUpdate($consentScope, $consentScope->getOriginal());
    }

    /**
     * Handle the ConsentScope "deleted" event.
     */
    public function deleted(ConsentScope $consentScope): void
    {
        $this->logDelete($consentScope);
    }
}
