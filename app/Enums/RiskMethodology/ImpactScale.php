<?php

namespace App\Enums\RiskMethodology;

enum ImpactScale: string
{
    case INSIGNIFICANT = 'insignificant';
    case MINOR = 'minor';
    case MODERATE = 'moderate';
    case MAJOR = 'major';
    case SEVERE = 'severe';
}
