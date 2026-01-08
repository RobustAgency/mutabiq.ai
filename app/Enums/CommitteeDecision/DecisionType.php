<?php

namespace App\Enums\CommitteeDecision;

enum DecisionType: string
{
    case APPROVE = 'approve';
    case DENY = 'deny';
    case WAIVE = 'waive';
    case POLICY = 'policy';
    case ESCALATE = 'escalate';
}
