<?php

namespace App\Enums\Framework;

enum Status: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case RETIRED = 'retired';
}
