<?php

namespace App\Enums\ConsentRecord;

enum Jurisdiction: string
{
    case EU = 'eu';
    case UAE = 'uae';
    case UK = 'uk';
    case KSA = 'ksa';
    case DIFC = 'difc';
    case US_CA = 'us_ca';
}
