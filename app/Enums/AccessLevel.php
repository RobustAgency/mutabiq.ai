<?php

namespace App\Enums;

enum AccessLevel: string
{
    case PUBLIC = 'public';
    case INTERNAL = 'internal';
    case RESTRICTED = 'restricted';
    case CONFIDENTIAL = 'confidential';
}
