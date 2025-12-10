<?php

namespace App\Enums\DataSubjectRequestAccess;

enum VerificationStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case FAILED = 'failed';
}
