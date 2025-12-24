<?php

namespace App\Enums\Vendor;

enum Type: string
{
    case MODEL_PROVIDER = 'model_provider';
    case DATASET_PROVIDER = 'dataset_provider';
    case INFRASTRUCTURE_CLOUD = 'infrastructure_cloud';
    case SAAS_PLATFORM = 'saas_platform';
    case CONSULTING_SERVICES = 'consulting_services';
    case HARDWARE_PROVIDER = 'hardware_provider';
    case API_SERVICE = 'api_service';
    case ANNOTATION_LABELING = 'annotation_labeling';
    case OTHER = 'other';
}
