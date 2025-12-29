<?php

namespace App\Enums\DataSource;

enum DataResidency: string
{
    case AE = 'ae'; // United Arab Emirates
    case EU = 'eu'; // European Union
    case KSA = 'ksa'; // Kingdom of Saudi Arabia
    case US = 'us'; // United States
    case UK = 'uk'; // United Kingdom
    case QA = 'qa'; // Qatar
    case JO = 'jo'; // Jordan
    case MA = 'ma'; // Morocco
    case BH = 'bh'; // Bahrain
    case OTHER = 'other';
}
