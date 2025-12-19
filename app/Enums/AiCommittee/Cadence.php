<?php

namespace App\Enums\AiCommittee;

enum Cadence: string
{
    case WEEKLY = 'weekly';
    case BIWEEKLY = 'biweekly';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case AD_HOC = 'ad_hoc';
}
