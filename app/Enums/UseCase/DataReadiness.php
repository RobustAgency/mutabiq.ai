<?php

namespace App\Enums\UseCase;

enum DataReadiness: string
{
    case READY_FOR_USE = 'ready_for_use';
    case REQUIRES_CLEANING = 'requires_cleaning';
    case REQUIRES_INTEGRATION = 'requires_integration';
    case REQUIRES_COLLECTION = 'requires_collection';
    case NOT_READY = 'not_ready';
}
