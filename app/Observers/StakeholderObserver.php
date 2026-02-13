<?php

namespace App\Observers;

use App\Models\Stakeholder;

class StakeholderObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'type',
            'display_name',
            'first_name',
            'last_name',
            'org_unit',
            'email',
            'secondary_email',
            'phone',
            'mobile',
            'role_tags',
            'timezone',
            'classification',
            'country',
            'external_ref',
            'employee_id',
            'cost_center',
            'manager',
            'delegate',
            'status',
            'notes',
            'start_date',
            'end_date',
        ];
    }

    public function created(Stakeholder $stakeholder): void
    {
        $this->logCreate($stakeholder);
    }

    public function updating(Stakeholder $stakeholder): void
    {
        $this->logUpdate($stakeholder, $stakeholder->getOriginal());
    }

    public function deleted(Stakeholder $stakeholder): void
    {
        $this->logDelete($stakeholder);
    }
}
