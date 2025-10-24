<?php

namespace App\Enums\DatasetElementMap;

enum PiiOverride: string
{
    case INHERIT = 'Inherit';
    case YES = 'Yes';
    case NO = 'No';
}
