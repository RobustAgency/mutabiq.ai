<?php

namespace App\Enums\DataSubjectRequestAccess;

enum VerificationMethod: string
{
    case EMAIL_LINK = 'email_link';
    case SMS_CODE = 'sms_code';
    case ID_DOCUMENT = 'id_document';
    case IN_PERSON = 'in_person';
    case CALLBACK = 'callback';
}
