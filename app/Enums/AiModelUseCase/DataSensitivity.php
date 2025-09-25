<?php

namespace App\Enums\AiModelUseCase;

enum DataSensitivity: string
{
    case PUBLIC = 'public';
    case INTERNAL = 'internal';
    case CONFIDENTIAL = 'confidential';
    case RESTRICTED = 'restricted';
}
