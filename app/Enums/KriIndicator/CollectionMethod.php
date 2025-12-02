<?php

namespace App\Enums\KriIndicator;

enum CollectionMethod: string
{
    case SCHEDULED_QUERY = 'scheduled_query';
    case STREAM_AGGREGATION = 'stream_aggregation';
    case BATCH_IMPORT = 'batch_import';
    case MANUAL_ENTRY = 'manual_entry';
}
