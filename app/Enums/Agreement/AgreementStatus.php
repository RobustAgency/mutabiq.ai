<?php

namespace App\Enums\Agreement;

enum AgreementStatus: string
{
    case DRAFT = 'draft';
    case UNDER_REVIEW = 'under_review';
    case PENDING_SIGNATURE = 'pending_signature';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case TERMINATED = 'terminated';
    case SUSPENDED = 'suspended';
}
