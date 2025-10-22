<?php

namespace App\Enums\DataSource;

enum DataClassification: string
{
    case PUBLIC = 'Public';
    case INTERNAL = 'Internal';
    case CONFIDENTIAL = 'Confidential';
    case RESTRICTED = 'Restricted';
}
