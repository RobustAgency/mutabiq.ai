<?php

namespace App\Enums\Agreement;

enum TransferMechanism: string
{
    case ADEQUACY = 'adequacy';
    case SCCS = 'sccs';
    case BCRS = 'bcrs';
    case DPA_ADDENDUM = 'dpa_addendum';
    case DEROGATION = 'derogation';
    case NONE = 'none';
}
