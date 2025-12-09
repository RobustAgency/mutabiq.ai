<?php

namespace App\Enums\RecordOfProcessingActivity;

enum Status: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case UNDER_REVIEW = 'under_review';
    case ARCHIVED = 'archived';
}
