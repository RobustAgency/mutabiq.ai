<?php

namespace App\Enums\IncidentNotification;

enum RegulatoryBasis: string
{
    case GDPR_ART_33 = 'gdpr_art_33';
    case GDPR_ART_34 = 'gdpr_art_34';
    case UAE_PDPL = 'uae_pdpl';
    case CONTRACTUAL = 'contractual';
    case INTERNAL_POLICY = 'internal_policy';
    case NA = 'na';
}
