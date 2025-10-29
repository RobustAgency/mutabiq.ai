<?php

namespace App\Enums;

enum AlertSourceType: string
{
    case KRI = 'kri';
    case MONITORING_RULE = 'monitoring_rule';
    case HUMAN_REPORT = 'human_report';
    case VENDOR_NOTICE = 'vendor_notice';
    case SECURITY_TOOL = 'security_tool';
    case OTHER = 'other';
}
