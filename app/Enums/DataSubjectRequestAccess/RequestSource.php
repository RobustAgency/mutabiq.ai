<?php

namespace App\Enums\DataSubjectRequestAccess;

enum RequestSource: string
{
    case EMAIL = 'email';
    case WEB_FORM = 'web_form';
    case PHONE = 'phone';
    case LETTER = 'letter';
    case IN_PERSON = 'in_person';
}
