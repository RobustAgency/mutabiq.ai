<?php

namespace App\Enums;

enum DeploymentStatus: string
{
    case NOT_DEPLOYED = 'not_deployed';
    case TESTING = 'testing';
    case STAGING = 'staging';
    case PRODUCTION = 'production';
    case RETIRED = 'retired';
}
