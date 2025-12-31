<?php

namespace App\Enums\AiIncident;

enum ImpactedDataType: string
{
    case PII_DIRECT_IDENTIFIERS = 'pii_direct_identifiers';
    case PII_CONTACT_INFORMATION = 'pii_contact_information';
    case PII_FINANCIAL = 'pii_financial';
    case PII_DEMOGRAPHIC = 'pii_demographic';
    case PII_BEHAVIORAL = 'pii_behavioral';
    case PII_LOCATION = 'pii_location';
    case SPECIAL_CATEGORY_HEALTH = 'special_category_health';
    case SPECIAL_CATEGORY_BIOMETRIC = 'special_category_biometric';
    case SPECIAL_CATEGORY_GENETIC = 'special_category_genetic';
    case SPECIAL_CATEGORY_POLITICAL = 'special_category_political';
    case SPECIAL_CATEGORY_RELIGIOUS = 'special_category_religious';
    case SPECIAL_CATEGORY_RACIAL_ETHNIC = 'special_category_racial_ethnic';
    case CONFIDENTIAL_BUSINESS_DATA = 'confidential_business_data';
    case INTERNAL_DATA = 'internal_data';
    case PUBLIC_DATA = 'public_data';
    case NONE_UNKNOWN = 'none_unknown';

}
