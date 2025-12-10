<?php

namespace App\Enums\DataProtectionImpactAssessment;

enum Jurisdiction: string
{
    case EU = 'eu';
    case UAE = 'uae';
    case UK = 'uk';
    case KSA = 'ksa';
    case DIFC = 'difc';
}
