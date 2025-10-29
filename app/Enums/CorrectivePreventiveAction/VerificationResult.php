<?php

namespace App\Enums\CorrectivePreventiveAction;

enum VerificationResult: string
{
    case PENDING = 'pending';
    case PASSED = 'passed';
    case FAILED = 'failed';
    case NOT_APPLICABLE = 'not_applicable';
}
