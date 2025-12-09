<?php

namespace App\Enums\RiskMethodology;

enum LikelihoodScale: string
{
    case RARE = 'rare';
    case UNLIKELY = 'unlikely';
    case POSSIBLE = 'possible';
    case LIKELY = 'likely';
    case ALMOST_CERTAIN = 'almost_certain';
}
