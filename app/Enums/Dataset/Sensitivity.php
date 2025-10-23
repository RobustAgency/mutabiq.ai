<?php

namespace App\Enums\Dataset;

enum Sensitivity: string
{
    case PUBLIC = 'Public';
    case INTERNAL = 'Internal';
    case CONFIDENTIAL = 'Confidential';
    case RESTRICTED = 'Restricted';
}
