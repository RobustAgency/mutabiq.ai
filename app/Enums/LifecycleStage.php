<?php

namespace App\Enums;

enum LifecycleStage: string
{
    case DEVELOPMENT = 'development';
    case TESTING = 'testing';
    case STAGING = 'staging';
    case PRODUCTION = 'production';
    case DEPRECATED = 'deprecated';
    case RETIRED = 'retired';
}
