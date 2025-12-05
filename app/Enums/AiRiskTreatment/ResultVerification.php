<?php

namespace App\Enums\AiRiskTreatment;

enum ResultVerification: string
{
    case PENDING = 'pending';
    case PASSED = 'passed';
    case FAILED = 'failed';
    case NOT_APPLICABLE = 'not_applicable';
}
