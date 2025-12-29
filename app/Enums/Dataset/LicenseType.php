<?php

namespace App\Enums\Dataset;

enum LicenseType: string
{
    case PROPRIETARY = 'proprietary';
    case OPEN_SOURCE = 'open_source';
    case PURCHASES = 'purchased';
    case COMMERCIAL_LICENSE = 'commercial_license';
    case RESEARCH_USE_ONLY = 'research_use_only';
    case NO_RESTRICTIONS = 'no_restrictions';
}
