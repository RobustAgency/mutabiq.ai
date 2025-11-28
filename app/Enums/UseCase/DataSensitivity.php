<?php

namespace App\Enums\UseCase;

enum DataSensitivity: string
{
    case PUBLIC = 'public';
    case INTERNAL = 'internal';
    case CONFIDENTIAL = 'confidential';
    case RESTRICTED = 'restricted';

    case PERSONAL_DATA = 'personal_data';
    case SENSITIVE_DATA = 'sensitive_data';
    case HIGHLY_SENSITIVE_DATA = 'highly_sensitive_data';

}
