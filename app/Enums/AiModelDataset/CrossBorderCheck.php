<?php

namespace App\Enums\AiModelDataset;

enum CrossBorderCheck: string
{
    case PASSED = 'passed';
    case FAILED = 'failed';
    case NOT_APPLICABLE = 'not_applicable';
}
