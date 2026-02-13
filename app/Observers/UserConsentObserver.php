<?php

namespace App\Observers;

use App\Models\UserConsent;

class UserConsentObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'subject_key',
            'subject_realm',
            'jurisdiction',
            'consent_purpose',
            'consent_status',
            'legal_basis',
            'source_system',
            'evidence_ref',
            'effective_from',
            'effective_to',
            'scope',
        ];
    }

    public function created(UserConsent $consent): void
    {
        $this->logCreate($consent);
    }

    public function updating(UserConsent $consent): void
    {
        $this->logUpdate($consent, $consent->getOriginal());
    }

    public function deleted(UserConsent $consent): void
    {
        $this->logDelete($consent);
    }
}
