<?php

namespace App\Enums;

enum OwnershipType: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
    case JOINT = 'joint';
    case LICENSED = 'licensed';
    case OPEN_SOURCE = 'open_source';
    case SAAS = 'saas';
}
