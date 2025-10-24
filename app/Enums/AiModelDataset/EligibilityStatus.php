<?php

namespace App\Enums\AiModelDataset;

enum EligibilityStatus: string
{
    case ELIGIBLE = 'eligible';
    case ELIGIBLE_WITH_CONDITIONS = 'eligible_with_conditions';
    case NOT_ELIGIBLE = 'not_eligible';
}
