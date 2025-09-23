<?php

namespace App\Enums;

enum VersionType: string
{
    case MAJOR = 'major';
    case MINOR = 'minor';
    case PATCH = 'patch';
    case EXPERIMENTAL = 'experimental';
}
