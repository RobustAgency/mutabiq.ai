<?php

namespace App\Enums;

enum ComplianceReviewStatus: string
{
    case PENDING = 'pending';
    case COMPLIANT = 'compliant';
    case NON_COMPLIANT = 'non_compliant';
}
