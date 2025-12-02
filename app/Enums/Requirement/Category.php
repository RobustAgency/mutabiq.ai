<?php

namespace App\Enums\Requirement;

enum Category: string
{
    case SAFETY = 'safety';
    case TRANSPARENCY = 'transparency';
    case DATA_OVERSIGHT = 'data_oversight';
    case SECURITY = 'security';
    case GOVERNANCE = 'governance';
    case RISK = 'risk';
    case TESTING = 'testing';
    case DOCUMENTATION = 'documentation';
    case PRIVACY = 'privacy';
    case HUMAN_RIGHTS = 'human_rights';
    case OTHER = 'other';
}
