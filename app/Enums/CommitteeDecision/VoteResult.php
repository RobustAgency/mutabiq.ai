<?php

namespace App\Enums\CommitteeDecision;

enum VoteResult: string
{
    case PASSED = 'passed';
    case FAILED = 'failed';
    case NOT_APPLICABLE = 'not_applicable';
}
