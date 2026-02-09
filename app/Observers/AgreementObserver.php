<?php

namespace App\Observers;

use App\Models\Agreement;

class AgreementObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'status',
            'agreement_owner_id',
            'contract_value',
            'effective_from',
            'effective_to',
            'renewal_type',
        ];
    }

    public function created(Agreement $agreement): void
    {
        $this->logCreate($agreement);
    }

    public function updating(Agreement $agreement): void
    {
        $this->logUpdate($agreement, $agreement->getOriginal());
    }

    public function deleted(Agreement $agreement): void
    {
        $this->logDelete($agreement);
    }
}
