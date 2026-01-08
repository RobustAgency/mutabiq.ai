<?php

namespace App\Enums\CommitteeAction;

enum ActionType: string
{
    case IMPLEMENT_CHANGE = 'implement_change';
    case COLLECT_EVIDENCE = 'collect_evidence';
    case UPDATE_POLICY = 'update_policy';
    case CONDUCT_ASSESSMENT = 'conduct_assessment';
    case NOTIFY_REGULATOR = 'notify_regulator';
    case OTHER = 'other';
}
