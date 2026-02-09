<?php

namespace App\Observers;

use App\Models\AiCommittee;

class AiCommitteeObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'name',
            'type',
            'cadence',
            'active',
        ];
    }

    public function created(AiCommittee $aiCommittee): void
    {
        $this->logCreate($aiCommittee);
    }

    public function updating(AiCommittee $aiCommittee): void
    {
        $this->logUpdate($aiCommittee, $aiCommittee->getOriginal());
    }

    public function deleted(AiCommittee $aiCommittee): void
    {
        $this->logDelete($aiCommittee);
    }
}
