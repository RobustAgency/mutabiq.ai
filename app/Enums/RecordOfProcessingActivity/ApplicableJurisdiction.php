<?php

namespace App\Enums\RecordOfProcessingActivity;

enum ApplicableJurisdiction: string
{
    case EU = 'eu';
    case UAE = 'uae';
    case UK = 'uk';
    case KSA = 'ksa';
    case DIFC = 'difc';
    case US_CA = 'us_ca';
    case OTHER = 'other';
}
