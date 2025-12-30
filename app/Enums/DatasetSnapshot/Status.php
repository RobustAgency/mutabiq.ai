<?php

namespace App\Enums\DatasetSnapshot;

enum Status: string
{
    case ACTIVE = 'active';
    case DEPRECATED = 'deprecated';
    case ARCHIVED = 'archived';
}
