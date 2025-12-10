<?php

namespace App\Enums\DataProtectionImpactAssessment;

enum Stage: string
{
    case SCREENING = 'screening';
    case NECESSITY = 'necessity';
    case RISK_IDENTIFICATION = 'risk_identification';
    case MITIGATION = 'mitigation';
    case DPO_CONSULTATION = 'dpo_consultation';
    case APPROVAL = 'approval';
    case COMPLETED = 'completed';
}
