<?php

namespace App\Enums\AiRiskRegister;

enum RiskStatus: string
{
    case IDENTIFIED = 'identified';
    case ASSESSED = 'assessed';
    case IN_TREATMENT = 'in_treatment';
    case ACCEPTED = 'accepted';
    case TRANSFERRED = 'transferred';
    case CLOSED = 'closed';
}
