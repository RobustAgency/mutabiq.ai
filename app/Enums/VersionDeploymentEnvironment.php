<?php

namespace App\Enums;

enum VersionDeploymentEnvironment: string
{
    case DEVELOPMENT = 'development';
    case TESTING = 'testing';
    case STAGING = 'staging';
    case PRODUCTION = 'production';

}
