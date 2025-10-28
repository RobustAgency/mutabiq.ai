<?php

namespace App\Enums\UserConsent;

enum LegalBasis: string
{
    case CONSENT = 'consent';
    case CONTRACT = 'contract';
    case LEGAL_OBLIGATION = 'legal_obligation';
    case LEGITIMATE_INTERESTS = 'legitimate_interests';
    case PUBLIC_TASK = 'public_task';
    case VITAL_INTERESTS = 'vital_interests';
}
