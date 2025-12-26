<?php

namespace App\Enums\Agreement;

enum ReplacesAgreement: string
{
    case DPA_OPENAI_2023 = 'dpa_openai_2023';
    case NONE = 'none';
}
