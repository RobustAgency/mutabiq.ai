<?php

namespace App\Enums\ConsentRecord;

enum Method: string
{
    case EXPLICIT_OPT_IN = 'explicit_opt_in';
    case IMPLIED = 'implied';
    case PRE_CHECKED = 'pre_checked';
    case VERBAL = 'verbal';
    case WRITTEN = 'written';
}
