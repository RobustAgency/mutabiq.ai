<?php

namespace App\Enums\Dataset;

enum CrossBorderTransfer: string
{
    case NONE = 'None';
    case ADEQUACY = 'Adequacy';
    case SCCS = 'SCCs';
    case DPA_ADDENDUM = 'DPA Addendum';
    case DEROGATION = 'Derogation';
}
