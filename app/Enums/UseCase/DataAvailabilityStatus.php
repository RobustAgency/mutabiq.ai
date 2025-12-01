<?php

namespace App\Enums\UseCase;

enum DataAvailabilityStatus: string
{
    case AVAILABLE = 'available';
    case PARTIALLY_AVAILABLE = 'partially_available';
    case NOT_AVAILABLE = 'not_available';
    case UNKNOWN = 'unknown';
}
