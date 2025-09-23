<?php

namespace App\Enums;

enum ValidationStatus: string
{
    case NOT_VALIDATED = 'not_validated';
    case IN_PROGRESS = 'in_progress';
    case PASSED = 'passed';
    case FAILED = 'failed';
}
