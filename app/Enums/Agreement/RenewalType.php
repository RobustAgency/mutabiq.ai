<?php

namespace App\Enums\Agreement;

enum RenewalType: string
{
    case AUTO_RENEWAL = 'auto_renewal';
    case MANUAL_RENEWAL = 'manual_renewal';
    case ONE_TIME_FIXED_TERM = 'one_time_fixed_term';
    case EVERGREEN = 'evergreen';
}
