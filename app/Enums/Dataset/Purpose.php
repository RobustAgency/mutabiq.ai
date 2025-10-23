<?php

namespace App\Enums\Dataset;

enum Purpose: string
{
    case TRAIN = 'train';
    case VAL = 'val';
    case TEST = 'test';
    case ONLINE = 'online';
    case ANALYTICS = 'analytics';
}
