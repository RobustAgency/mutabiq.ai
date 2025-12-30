<?php

namespace App\Enums\Dataset;

enum SizeUnit: string
{
    case BYTES = 'bytes';
    case KILOBYTES = 'kilobytes';
    case MEGABYTES = 'megabytes';
    case GIGABYTES = 'gigabytes';
    case TERABYTES = 'terabytes';
}
