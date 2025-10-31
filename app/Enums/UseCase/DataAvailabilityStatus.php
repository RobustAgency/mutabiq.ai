<?php

namespace App\Enums\UseCase;

enum DataAvailabilityStatus: string
{
    case AVAILABLE = 'available';
    case PARTIALLY_AVAILABLE = 'partially available';
    case NOT_AVAILABLE = 'not available';
}
