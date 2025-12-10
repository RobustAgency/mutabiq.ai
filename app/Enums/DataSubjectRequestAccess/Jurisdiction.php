<?php

namespace App\Enums\DataSubjectRequestAccess;

enum Jurisdiction: string
{
    case EU = 'eu';
    case UAE = 'uae';
    case DIFC = 'difc';
    case UK = 'uk';
    case KSA = 'ksa';
    case US_CA = 'us-ca';
}
