<?php

namespace App\Enums;

enum CreatorRole: string
{
    case INTERNAL_TEAM = 'internal_team';
    case VENDOR_PROVIDED = 'vendor_provided';
    case COMMUNITY_CONTRIBUTED = 'community_contributed';
    case AUTO_GENERATED = 'auto_generated';
}
