<?php

namespace App\Enums\UseCase;

enum DataSensitivity: string
{
    case PUBLIC = 'public';
    case INTERNAL = 'internal';
    case CONFIDENTIAL = 'confidential';
    case RESTRICTED = 'restricted';
}
