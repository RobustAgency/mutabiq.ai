<?php

namespace App\Enums\Agreement;

enum TrainingOptOut: string
{
    case PROHIBITED = 'prohibited';
    case ALLOWED_WITH_CONSENT = 'allowed_with_consent';
    case ALLOWED_WITH_PRE_TERMS = 'allowed_with_pre_terms';
    case NOT_APPLICABLE = 'not_applicable';
    case NOT_SPECIFIED = 'not_specified';
}
