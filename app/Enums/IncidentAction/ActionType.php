<?php

namespace App\Enums\IncidentAction;

enum ActionType: string
{
    case KILL_SWITCH = 'kill_switch';
    case MODEL_ROLLBACK = 'model_rollback';
    case DATA_ISOLATION = 'data_isolation';
    case ACCESS_REVOCATION = 'access_revocation';
    case SYSTEM_PATCH = 'system_patch';
    case CONFIGURATION_CHANGE = 'configuration_change';
    case COMMUNICATION_NOTIFICATION = 'communication_notification';
    case INVESTIGATION = 'investigation';
    case CONTAINMENT = 'containment';
    case ERADICATION = 'eradication';
    case RECOVERY = 'recovery';
    case DOCUMENTATION = 'documentation';
    case OTHER = 'other';
}
