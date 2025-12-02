<?php

namespace App\Enums\Control;

enum Status: string
{
    case PROPOSED = 'proposed';
    case IMPLEMENTED = 'implemented';
    case DEPRECATED = 'deprecated';
}
