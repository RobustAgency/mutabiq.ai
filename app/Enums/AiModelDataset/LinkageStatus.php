<?php

namespace App\Enums\AiModelDataset;

enum LinkageStatus: string
{
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case ACTIVE = 'active';
    case DEPRECATED = 'deprecated';
    case ARCHIVED = 'archived';
}
