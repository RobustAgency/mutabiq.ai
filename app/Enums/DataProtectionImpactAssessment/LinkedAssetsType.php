<?php

namespace App\Enums\DataProtectionImpactAssessment;

enum LinkedAssetsType: string
{
    case AI_MODEL = 'ai_model';
    case SYSTEM = 'system';
    case PROCESS = 'process';
    case TECHNOLOGY = 'technology';
}
