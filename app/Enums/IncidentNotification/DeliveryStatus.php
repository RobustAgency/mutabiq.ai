<?php

namespace App\Enums\IncidentNotification;

enum DeliveryStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case ACKNOWLEDGED = 'acknowledged';
    case FAILED = 'failed';
}
