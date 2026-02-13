<?php

namespace App\Observers;

use App\Models\TeamInvitation;

class TeamInvitationObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'invited_by',
            'email',
            'role',
            'status',
            'expires_at',
        ];
    }

    public function created(TeamInvitation $invitation): void
    {
        $this->logCreate($invitation);
    }

    public function updating(TeamInvitation $invitation): void
    {
        $this->logUpdate($invitation, $invitation->getOriginal());
    }

    public function deleted(TeamInvitation $invitation): void
    {
        $this->logDelete($invitation);
    }
}
