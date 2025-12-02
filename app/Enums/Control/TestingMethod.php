<?php

namespace App\Enums\Control;

enum TestingMethod: string
{
    case DESIGN = 'design';
    case OPERATING = 'operating';
    case BOTH = 'both';
}
