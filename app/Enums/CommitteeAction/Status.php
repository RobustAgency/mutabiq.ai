<?php

namespace App\Enums\CommitteeAction;

enum Status: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
