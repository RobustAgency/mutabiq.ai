<?php

namespace App\Enums\IncidentNotification;

enum Channel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case PORTAL_NOTIFICATION = 'portal_notification';
    case SLACK_TEAMS = 'slack_teams';
    case FORMAL_LETTER = 'formal_letter';
    case PRESS_RELEASE = 'press_release';
    case REGULATORY_FILING = 'regulatory_filing';
}
