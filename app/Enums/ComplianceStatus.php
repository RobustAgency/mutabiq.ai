<?php

namespace App\Enums;

enum ComplianceStatus: string
{
    case COMPLIANT = 'compliant';
    case NON_COMPLIANT = 'non_compliant';
    case UNDER_REVIEW = 'under_review';
    case NOT_CHECKED = 'not_checked';
}
