<?php

namespace App\Enums;

enum ArtifactEnvironment: string
{
    case DEVELOPMENT = 'development';
    case TESTING = 'testing';
    case STAGING = 'staging';
    case PRODUCTION = 'production';
    case SHARED = 'shared';
    case ARCHIVE = 'archive';
}
