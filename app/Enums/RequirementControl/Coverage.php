<?php

namespace App\Enums\RequirementControl;

enum Coverage: string
{
    case FULL = 'full';
    case PARTIAL = 'partial';
    case NOT_APPLICABLE = 'not_applicable';
}
