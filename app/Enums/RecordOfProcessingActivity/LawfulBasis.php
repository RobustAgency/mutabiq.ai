<?php

namespace App\Enums\RecordOfProcessingActivity;

enum LawfulBasis: string
{
    case CONSENT = 'consent';
    case CONTRACT = 'contract';
    case LEGITIMATE_INTEREST = 'legitimate_interest';
    case LEGAL_OBLIGATION = 'legal_obligation';
    case PUBLIC_TASK = 'public_task';
    case VITAL_INTEREST = 'vital_interest';
}
