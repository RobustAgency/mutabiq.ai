<?php

namespace App\Enums\ConsentRecord;

enum Purpose: string
{
    case MARKETING = 'marketing';
    case AI_TRAINING = 'ai_training';
    case PROFILING = 'profiling';
    case PERSONALIZATION = 'personalization';
    case BIOMETRICS = 'biometrics';
    case CCTV = 'cctv';
    case ANALYTICS = 'analytics';
    case RESEARCH = 'research';
}
