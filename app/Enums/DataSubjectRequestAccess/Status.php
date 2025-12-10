<?php

namespace App\Enums\DataSubjectRequestAccess;

enum Status: string
{
    case NEW = 'new';
    case PENDING_VERIFICATION = 'pending_verification';
    case IN_PROGRESS = 'in_progress';
    case PENDING_APPROVAL = 'pending_approval';
    case READY_FOR_RESPONSE = 'ready_for_response';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
