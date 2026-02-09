<?php

namespace App\Observers;

use App\Models\CommitteeMembership;

class CommitteeMembershipObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'eligibility',
            'member_role',
            'start_date',
            'end_date',
            'expertise_tags',
        ];
    }

    public function created(CommitteeMembership $committeeMembership): void
    {
        $this->logCreate($committeeMembership);
    }

    public function updating(CommitteeMembership $committeeMembership): void
    {
        $this->logUpdate($committeeMembership, $committeeMembership->getOriginal());
    }

    public function deleted(CommitteeMembership $committeeMembership): void
    {
        $this->logDelete($committeeMembership);
    }
}
