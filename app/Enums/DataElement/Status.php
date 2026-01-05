<?php

namespace App\Enums\DataElement;

enum Status: string
{
    case ACTIVE = 'active';
    case DEPRECATED = 'deprecated';
    case RETIRED = 'retired';
}
