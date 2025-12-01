<?php

namespace App\Enums\AiRiskTreatment;

enum TreatmentType: string
{
    case CORRECTIVE = 'corrective';
    case PREVENTIVE = 'preventive';
    case DETECTIVE = 'detective';
    case TRANSFER_INSURANCE = 'transfer_insurance';
    case TRANSFER_VENDOR = 'transfer_vendor';
    case AVOID_CHANGE = 'avoid_change';
    case OTHER = 'other';
}
