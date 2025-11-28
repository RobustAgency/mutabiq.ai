<?php

namespace App\Enums\UseCase;

enum Status: string
{
    case DRAFT = 'draft';
    case STAGING = 'staging';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ON_HOLD = 'on_hold';
    case IN_PRODUCTION = 'in_production';
    case RETIRED = 'retired';
}
