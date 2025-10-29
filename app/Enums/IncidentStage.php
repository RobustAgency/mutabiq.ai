<?php

namespace App\Enums;

enum IncidentStage: string
{
    case IDEATION = 'ideation';
    case CONCEPTION = 'conception';
    case DEV = 'dev';
    case TEST = 'test';
    case STAGING = 'staging';
    case PROD = 'prod';
    case RETIREMENT = 'retirement';
}
