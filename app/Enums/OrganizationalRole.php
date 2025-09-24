<?php

namespace App\Enums;

enum OrganizationalRole: string
{
    case DEVELOPER = 'developer';
    case IMPORTER = 'importer';
    case DEPLOYER = 'deployer';
    case INTEGRATOR = 'integrator';
    case CONSUMER = 'consumer';
    case COLLABORATOR = 'collaborator';
}
