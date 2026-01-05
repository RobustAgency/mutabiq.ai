<?php

namespace App\Enums\DataElement;

enum PersonalDataCategory: string
{
    case DIRECT_IDENTIFIER = 'direct_identifier';
    case CONTACT_INFORMATION = 'contact_information';
    case FINANCIAL_DATA = 'financial_data';
    case DEMOGRAPHIC = 'demographic';
    case BEHAVIORAL = 'behavioral';
    case LOCATION = 'location';
    case BIOMETRIC = 'biometric';
    case HEALTH = 'health';
    case GENETIC = 'genetic';
    case POLITICAL = 'political';
    case RELIGIOUS = 'religious';
    case RACIAL = 'racial';
    case SEXUAL = 'sexual';
    case CRIMINAL = 'criminal';
    case CHILDREN = 'children';
}
