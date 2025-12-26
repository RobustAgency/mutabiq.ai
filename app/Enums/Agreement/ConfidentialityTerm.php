<?php

namespace App\Enums\Agreement;

enum ConfidentialityTerm: string
{
    case STRICT = 'strict';
    case MODERATE = 'moderate';
    case LENIENT = 'lenient';
}
