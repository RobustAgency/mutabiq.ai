<?php

namespace App\Enums\Stakeholder;

enum Status: string
{
    case ACTIVE = 'active';
    case IN_ACTIVE = 'in_active';
    case ON_LEAVE = 'on_leave';
    case OFF_BOARDED = 'off_boarded';
}
