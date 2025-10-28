<?php

namespace App\Enums\DatasetElementMap;

enum SensitivityOverride: string
{
    case PUBLIC = 'Public';
    case INTERNAL = 'Internal';
    case CONFIDENTIAL = 'Confidential';
    case RESTRICTED = 'Restricted';
}
