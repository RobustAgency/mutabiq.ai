<?php

namespace App\Enums;

enum LifecycleStage: string
{
    case DEVELOPMENT = 'development';
    case DESIGN = 'design';
    case VALIDATION = 'validation';
    case DEPLOYMENT = 'deployment';
    case MONITORING = 'monitoring';
    case RETIRED = 'retired';
}
