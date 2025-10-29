<?php

namespace App\Enums\IncidentAction;

enum ValidationResult: string
{
    case PASSED = 'passed';
    case FAILED = 'failed';
    case PENDING = 'pending';
    case NOT_APPLICABLE = 'not_applicable';
}
