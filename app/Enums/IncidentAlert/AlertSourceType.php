<?php

namespace App\Enums\IncidentAlert;

enum AlertSourceType: string
{
    case MONITORING_RULE = 'monitoring_rule';
    case KRI_THRESHOLD = 'kri_threshold';
    case MANUAL_REPORT = 'manual_report';
    case AUTOMATED_SCAN = 'automated_scan';
    case USER_COMPLAINT = 'user_complaint';
    case EXTERNAL_REPORT = 'external_report';
}
