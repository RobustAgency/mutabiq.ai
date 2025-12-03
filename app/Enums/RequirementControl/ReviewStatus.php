<?php

namespace App\Enums\RequirementControl;

enum ReviewStatus: string
{
    case DRAFT = 'draft';
    case PEER_REVIEWED = 'peer_reviewed';
    case APPROVED = 'approved';
}
