<?php

namespace App\Enums\Dataset;

enum LicenseType: string
{
    case PROPRIETARY = 'Proprietary';
    case OPEN_PERMISSIVE = 'Open (permissive)';
    case OPEN_COPYLEFT = 'Open (copyleft)';
    case COMMERCIAL = 'Commercial';
    case DATASET_EULA = 'Dataset EULA';
}
