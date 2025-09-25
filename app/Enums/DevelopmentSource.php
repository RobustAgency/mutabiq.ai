<?php
namespace App\Enums;

enum DevelopmentSource: string
{
    case INTERNAL_DEVELOPMENT = 'internal_development';
    case EXTERNAL_VENDOR = 'external_vendor';
    case OPEN_SOURCE_COMMUNITY = 'open_source_community';
    case CLOUD_PROVIDER = 'cloud_provider';
    case PARTNERSHIP = 'partnership';
}