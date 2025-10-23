<?php

namespace App\Enums\DataSource;

enum SystemType: string
{
    case APPLICATION_DB = 'Application DB';
    case DATA_LAKE = 'Data Lake';
    case DATA_WAREHOUSE = 'Data Warehouse';
    case OPERATIONAL_API = 'Operational API';
    case FILES_BUCKETS = 'Files/Buckets';
    case THIRD_PARTY_SAAS = '3rd-Party SaaS';
    case STREAMING_KAFKA = 'Streaming/Kafka';
}
