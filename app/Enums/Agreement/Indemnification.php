<?php

namespace App\Enums\Agreement;

enum Indemnification: string
{
    case VENDOR_INDEMNIFIES = 'vendor_indemnifies';
    case MUTUAL = 'mutual';
    case LIMITED = 'limited';
    case NONE = 'none';
}
