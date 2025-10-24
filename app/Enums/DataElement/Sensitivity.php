<?php

namespace App\Enums\DataElement;

enum Sensitivity: string
{
    case PUBLIC = 'Public';
    case INTERNAL = 'Internal';
    case CONFIDENTIAL = 'Confidential';
    case RESTRICTED = 'Restricted';
}
