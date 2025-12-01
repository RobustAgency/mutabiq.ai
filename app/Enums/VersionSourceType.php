<?php

namespace App\Enums;

enum VersionSourceType: string
{
    case INTERNAL_DEVELOPMENT = 'internal_development';
    case VENDOR_MODEL = 'vendor_model';
    case OPEN_SOURCE = 'open_source';
    case FOUNDATION_MODEL = 'foundation_model';
}
