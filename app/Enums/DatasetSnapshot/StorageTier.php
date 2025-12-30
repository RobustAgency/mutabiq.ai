<?php

namespace App\Enums\DatasetSnapshot;

enum StorageTier: string
{
    case HOT = 'hot';
    case COLD = 'cold';
    case WARM = 'warm';
    case ARCHIVE = 'archive';
}
