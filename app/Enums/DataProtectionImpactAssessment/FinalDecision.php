<?php

namespace App\Enums\DataProtectionImpactAssessment;

enum FinalDecision: string
{
    case APPROVED = 'approved';
    case APPROVED_WITH_CONDITIONS = 'approved_with_conditions';
    case REJECTED = 'rejected';
    case DEFERRED = 'deferred';
}
