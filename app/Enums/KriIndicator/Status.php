<?php

namespace App\Enums\KriIndicator;

enum Status: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case RETIRED = 'retired';
}
