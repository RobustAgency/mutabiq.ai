<?php

namespace App\Enums\CommitteeDecision;

enum DecisionScope: string
{
    case MODEL = 'model';
    case USE_CASE = 'use_case';
    case CONTROL = 'control';
    case POLICY = 'policy';
    case VENDOR = 'vendor';
    case RELEASE = 'release';
    case ASSESSMENT = 'assessment';
    case OTHER = 'other';
}
