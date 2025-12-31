<?php

namespace App\Enums\AiIncident;

enum ResidencyAffected: string
{
    case AE = 'ae';
    case EU = 'eu';
    case KSA = 'ksa';
    case US = 'us';
    case UK = 'uk';
    case QA = 'qa';
    case JO = 'jo';
    case MA = 'ma';
    case BH = 'bh';
    case OTHER = 'other';
    case MULTIPLE = 'multiple';
}
