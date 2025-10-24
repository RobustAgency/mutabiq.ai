<?php

namespace App\Enums\DataElement;

enum PiiFlag: string
{
    case YES = 'Yes';
    case NO = 'No';
    case MAY_CONTAIN = 'May_Contain';
}
