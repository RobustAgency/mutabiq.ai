<?php

namespace App\Enums\PrivacyIncident;

enum IncidentType: string
{
    case UNAUTHORIZED_ACCESS = 'unauthorized_access';
    case DATA_EXPOSURE = 'data_exposure';
    case LOST_DEVICE = 'lost_device';
    case STOLEN_DEVICE = 'stolen_device';
    case MISDELIVERY = 'misdelivery';
    case RANSOMWARE = 'ransomware';
    case PHISHING = 'phishing';
    case SYSTEM_BREACH = 'system_breach';
    case HUMAN_ERROR = 'human_error';
    case THIRD_PARTY = 'third_party';
}
