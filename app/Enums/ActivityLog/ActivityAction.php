<?php

namespace App\Enums\ActivityLog;

enum ActivityAction: string
{
    case CREATE = 'CREATE';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
}
