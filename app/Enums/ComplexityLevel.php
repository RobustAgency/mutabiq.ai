<?php

namespace App\Enums;

enum ComplexityLevel: string
{
    case SIMPLE = 'simple';
    case MODERATE = 'moderate';
    case COMPLEX = 'complex';
    case MASSIVE = 'massive';
}
