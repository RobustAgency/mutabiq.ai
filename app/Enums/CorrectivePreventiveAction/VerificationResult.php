<?php

namespace App\Enums\CorrectivePreventiveAction;

enum VerificationResult: string
{
    case PENDING = 'pending';
    case VERIFIED_EFFECTIVE = 'verified_effective';
    case REQUIRES_REWORK = 'requires_rework';
    case VERIFIED_INEFFECTIVE = 'verified_ineffective';
}
