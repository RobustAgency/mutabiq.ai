<?php

namespace App\Enums\CommitteeDecision;

enum VoteMethod: string
{
    case SIMPLE_MAJORITY = 'simple_majority';
    case SUPER_MAJORITY = 'super_majority';
    case CONSENSUS = 'consensus';
    case CHAIR_DECISION = 'chair_decision';
}
