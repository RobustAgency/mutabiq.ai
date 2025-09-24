<?php

namespace App\Enums;

enum TechnicalReviewStatus: string
{
    case PENDING = 'pending';
    case PASSED = 'passed';
    case FAILED = 'failed';
}
