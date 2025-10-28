<?php

namespace App\Enums\DataElement;

enum PersonalDataCategory: string
{
    case IDENTIFIER = 'Identifier';
    case CONTACT = 'Contact';
    case FINANCIAL = 'Financial';
    case BEHAVIORAL = 'Behavioral';
    case LOCATION = 'Location';
    case BIOMETRIC = 'Biometric';
    case HEALTH = 'Health';
    case SENSITIVE_OTHER = 'Sensitive-Other';
}
