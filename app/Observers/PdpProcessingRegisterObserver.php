<?php

namespace App\Observers;

use App\Models\PdpProcessingRegister;

class PdpProcessingRegisterObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'purpose',
            'controller_role',
            'data_subject_categories',
            'personal_data_categories',
            'lawful_basis',
            'lawful_basis_detail',
            'retention_policy_ref',
            'recipients',
            'international_transfer_ref',
            'dpia_required_flag',
            'security_measures_ref',
            'owner_team',
            'effective_from',
            'effective_to',
            'status',
        ];
    }

    public function created(PdpProcessingRegister $register): void
    {
        $this->logCreate($register);
    }

    public function updating(PdpProcessingRegister $register): void
    {
        $this->logUpdate($register, $register->getOriginal());
    }

    public function deleted(PdpProcessingRegister $register): void
    {
        $this->logDelete($register);
    }
}
