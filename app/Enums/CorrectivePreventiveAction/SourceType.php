<?php

namespace App\Enums\CorrectivePreventiveAction;

enum SourceType: string
{
    case INCIDENT = 'incident';
    case RISK = 'risk';
    case FEEDBACK = 'feedback';
    case OVERRIDE = 'override';
    case AUDIT = 'audit';
    case ASSESSMENT = 'assessment';
    case OTHER = 'other';
}
