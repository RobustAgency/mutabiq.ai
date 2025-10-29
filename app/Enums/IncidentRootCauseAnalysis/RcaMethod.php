<?php

namespace App\Enums\IncidentRootCauseAnalysis;

enum RcaMethod: string
{
    case FIVE_WHYS = '5_whys';
    case FISHBONE = 'fishbone';
    case TIMELINE_ANALYSIS = 'timeline_analysis';
    case FAULT_TREE = 'fault_tree';
    case OTHER = 'other';
}
