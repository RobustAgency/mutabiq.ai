<?php

namespace App\Enums\RecordOfProcessingActivity;

enum DataCategory: string
{
    case NAME = 'name';
    case CONTACT = 'contact';
    case IDENTIFIER = 'identifier';
    case FINANCIAL = 'financial';
    case HEALTH = 'health';
    case BIOMETRIC = 'biometric';
    case BEHAVIORAL = 'behavioral';
    case SENSITIVE = 'sensitive';
    case CHILDREN = 'children';
    case LOCATION = 'location';
}
