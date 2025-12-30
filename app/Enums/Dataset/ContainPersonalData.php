<?php

namespace App\Enums\Dataset;

enum ContainPersonalData: string
{
    case YES = 'yes';
    case NO = 'no';
    case UNKNOWN = 'unknown';
}
