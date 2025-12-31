<?php

namespace App\Enums\IncidentAction;

enum ValidationResult: string
{
    case PENDING = 'pending';
    case PARTIALLY_EFFECTIVE = 'partially_effective';
    case EFFECTIVE = 'effective';
    case INEFFECTIVE = 'ineffective';
}
