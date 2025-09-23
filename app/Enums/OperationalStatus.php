<?php

namespace App\Enums;

enum OperationalStatus: string
{
    case NOT_DEPLOYED = 'not_deployed';
    case DEVELOPMENT = 'development';
    case TESTING = 'testing';
    case PRODUCTION = 'production';
}
