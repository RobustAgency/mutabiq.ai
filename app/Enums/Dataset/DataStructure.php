<?php

namespace App\Enums\Dataset;

enum DataStructure: string
{
    case STRUCTURED = 'structured';
    case SEMI_STRUCTURED = 'semi_structured';
    case UNSTRUCTURED = 'unstructured';
}
