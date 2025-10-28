<?php

namespace App\Enums\UserConsent;

enum ConsentPurpose: string
{
    case MARKETING = 'marketing';
    case ANALYTICS = 'analytics';
    case PERSONALIZATION = 'personalization';
    case TRAINING_AI = 'training_ai';
    case SERVICE_OPERATIONS = 'service_operations';
    case SUPPORT = 'support';
    case RESEARCH = 'research';
    case OTHER = 'other';
}
