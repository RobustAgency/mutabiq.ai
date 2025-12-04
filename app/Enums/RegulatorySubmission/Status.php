<?php

namespace App\Enums\RegulatorySubmission;

enum Status: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case ACKNOWLEDGED = 'acknowledged';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CLOSED = 'closed';
}
