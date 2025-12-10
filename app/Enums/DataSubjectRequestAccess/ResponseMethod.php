<?php

namespace App\Enums\DataSubjectRequestAccess;

enum ResponseMethod: string
{
    case EMAIL = 'email';
    case SECURE_PORTAL = 'secure_portal';
    case ENCRYPTED_FILE = 'encrypted_file';
    case PHYSICAL_MAIL = 'physical_mail';
    case IN_PERSON = 'in_person';
}
