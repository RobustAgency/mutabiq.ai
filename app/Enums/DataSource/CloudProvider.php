<?php

namespace App\Enums\DataSource;

enum CloudProvider: string
{
    case AWS = 'aws';
    case AZURE = 'azure';
    case GCP = 'gcp';
    case OTHER = 'other';
    case NONE = 'none';
}
