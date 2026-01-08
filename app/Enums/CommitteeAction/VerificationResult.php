<?php

namespace App\Enums\CommitteeAction;

enum VerificationResult: string
{
    case PENDING = 'pending';
    case PASSED = 'passed';
    case FAILED = 'failed';
    case NOT_APPLICABLE = 'not_applicable';
}
