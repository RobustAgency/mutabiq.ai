<?php

namespace App\Enums;

enum BusinessStatus: string
{
    case PLANNED = 'planned';
    case ACTIVE = 'active';
    case DEPRECATED = 'deprecated';
    case RETIRED = 'retired';
}
