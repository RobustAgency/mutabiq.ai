<?php

namespace App\Enums\DataSubjectRequestAccess;

enum RequestType: string
{
    case ACCESS = 'access';
    case RECTIFICATION = 'rectification';
    case ERASURE = 'erasure';
    case RESTRICTION = 'restriction';
    case PORTABILITY = 'portability';
    case OBJECTION = 'objection';
    case OPT_OUT_MARKETING = 'opt_out_marketing';
    case AI_EXPLAINABILITY = 'ai_explainability';
    case AUTOMATED_DECISION_CHALLENGE = 'automated_decision_challenge';
}
