<?php

namespace App\Enums;

enum VersionOrgInvolvement: string
{
    case FULL_DEVELOPMENT = 'full_development';
    case FINE_TUNING = 'fine_tuning';
    case CONFIGURATION_ONLY = 'configuration_only';
    case INTEGRATION_ONLY = 'integration_only';

}
