<?php

namespace App\Enums\AiModelDataset;

enum SpecialCategoryCheck: string
{
    case PASSED = 'passed';
    case FAILED = 'failed';
    case NOT_APPLICABLE = 'not_applicable';
}
