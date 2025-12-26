<?php

namespace App\Enums\Agreement;

enum DisputeResolution: string
{
    case MEDIATION_THEN_ARBITRATION = 'mediation_then_arbitration';
    case ARBITRATION = 'arbitration';
    case COURTS = 'courts';
}
