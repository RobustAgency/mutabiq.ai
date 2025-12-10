<?php

namespace App\Enums\DataProtectionImpactAssessment;

enum Status: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'in_progress';
    case DPO_REVIEW = 'dpo_review';
    case PENDING_APPROVAL = 'pending_approval';
    case COMPLETED = 'completed';
    case ARCHIVED = 'archived';
}
