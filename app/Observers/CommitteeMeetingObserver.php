<?php

namespace App\Observers;

use App\Models\CommitteeMeeting;

class CommitteeMeetingObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'meeting_type',
            'scheduled_at',
            'duration_minutes',
            'agenda',
            'materials_link',
            'attendance_policy',
            'attendance_roster',
            'minutes_link',
        ];
    }

    public function created(CommitteeMeeting $committeeMeeting): void
    {
        $this->logCreate($committeeMeeting);
    }

    public function updating(CommitteeMeeting $committeeMeeting): void
    {
        $this->logUpdate($committeeMeeting, $committeeMeeting->getOriginal());
    }

    public function deleted(CommitteeMeeting $committeeMeeting): void
    {
        $this->logDelete($committeeMeeting);
    }
}
