<?php

namespace App\Enums\IncidentRootCauseAnalysis;

enum RcaMethod: string
{
    case FIVE_WHYS = 'five_whys';
    case FISHBONE = 'fishbone';
    case FAULT_TREE = 'fault_tree';
    case EVENT_CAUSAL = 'event_causal';
    case CHANGE = 'change';
    case TIMELINE = 'timeline';
    case BARRIER = 'barrier';
    case COMBINED = 'combined';
}
