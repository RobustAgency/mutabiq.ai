<?php

namespace App\Enums\AiModelDataset;

enum ConsentCheckStatus: string
{
    case PASSED = 'passed';
    case WARNING = 'warning';
    case FAILED = 'failed';
    case NOT_APPLICABLE = 'not_applicable';
}
