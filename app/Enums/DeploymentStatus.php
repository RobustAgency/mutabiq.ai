<?php

namespace App\Enums;

enum DeploymentStatus: string
{
    case NOT_DEPLOYED = 'not_deployed';
    case DEPLOYING = 'deploying';
    case DEPLOYED = 'deployed';
    case FAILED = 'failed';
    case ROLLBACK = 'rollback';
}
