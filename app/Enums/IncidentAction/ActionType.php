<?php

namespace App\Enums\IncidentAction;

enum ActionType: string
{
    case KILL_SWITCH = 'kill_switch';
    case ROLLBACK_RELEASE = 'rollback_release';
    case KEY_ROTATION = 'key_rotation';
    case BLOCKLIST_UPDATE = 'blocklist_update';
    case TRAFFIC_THROTTLE = 'traffic_throttle';
    case MODEL_DISABLE_TOOL = 'model_disable_tool';
    case POLICY_CHANGE = 'policy_change';
    case COMMUNICATION = 'communication';
    case DATA_PURGE = 'data_purge';
    case OTHER = 'other';
}
