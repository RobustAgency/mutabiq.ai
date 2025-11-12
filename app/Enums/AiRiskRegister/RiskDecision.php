<?php

namespace App\Enums\AiRiskRegister;

enum RiskDecision: string
{
    case TREAT = 'treat';
    case ACCEPT = 'accept';
    case TRANSFER = 'transfer';
    case AVOID = 'avoid';
}
