<?php

namespace App\Enums;

enum VersionApprovalStatus: string
{
    case PENDING_REVIEW = 'pending_review';
    case APPROVED_FOR_PILOT = 'approved_for_pilot';
    case APPROVED_FOR_PRODUCTION = 'approved_for_production';
    case REJECTED = 'rejected';
    case ROLLED_BACK = 'rolled_back';

}
