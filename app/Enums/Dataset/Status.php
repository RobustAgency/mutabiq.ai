<?php

namespace App\Enums\Dataset;

enum Status: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case UNDER_REVIEW = 'under_review';
    case DEPRECATED = 'deprecated';
    case ARCHIVED = 'archived';
}
