<?php

namespace App\Enums\AiIncident;

enum ExternalParty: string
{
    case CLOUD_SERVICE_PROVIDER = 'cloud_service_provider';
    case DATA_PROCESSOR = 'data_processor';
    case SOFTWARE_VENDOR = 'software_vendor';
    case HARDWARE_VENDOR = 'hardware_vendor';
    case CONSULTING_PARTNER = 'consulting_partner';
    case CUSTOMER = 'customer';
    case BUSINESS_PARTNER = 'business_partner';
    case REGULATOR = 'regulator';
    case AUDITOR = 'auditor';
    case INSURANCE_PROVIDER = 'insurance_provider';
    case NONE = 'none';
}
