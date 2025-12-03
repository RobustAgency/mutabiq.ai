<?php

namespace App\Enums\ComplianceEvidence;

enum ReviewOutcome: string
{
    case PASS = 'pass';
    case FAIL = 'fail';
    case NEEDS_FIX = 'needs_fix';
}
