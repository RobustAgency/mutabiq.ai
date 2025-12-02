<?php

namespace App\Enums\KriIndicator;

enum ActionOnBreach: string
{
    case NOTIFY_ONLY = 'notify_only';
    case OPEN_INCIDENT = 'open_incident';
    case ESCALATE_COMMITTEE = 'escalate_committee';
    case TRIGGER_ASSESSMENT = 'trigger_assessment';
    case AUTO_KILL_SWITCH = 'auto_kill_switch';
}
