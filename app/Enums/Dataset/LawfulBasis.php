<?php

namespace App\Enums\Dataset;

enum LawfulBasis: string
{
    case CONSENT = 'Consent';
    case CONTRACT = 'Contract';
    case LEGAL_OBLIGATION = 'Legal Obligation';
    case LEGITIMATE_INTERESTS = 'Legitimate Interests';
    case PUBLIC_TASK = 'Public Task';
    case VITAL_INTERESTS = 'Vital Interests';
}
