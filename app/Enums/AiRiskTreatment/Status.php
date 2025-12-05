<?php

namespace App\Enums\AiRiskTreatment;

enum Status: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case PENDING_VERIFICATION = 'pending_verification';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
}
