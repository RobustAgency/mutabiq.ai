<?php

namespace App\Enums\IncidentAction;

enum ApprovalRequired: string
{
    case NO_APPROVAL_NEEDED = 'no_approval_needed';
    case MANAGER_APPROVAL = 'manager_approval';
    case EXECUTIVE_APPROVAL = 'executive_approval';
    case LEGAL_APPROVAL = 'legal_approval';
}
