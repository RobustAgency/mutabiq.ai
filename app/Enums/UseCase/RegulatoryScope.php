<?php

namespace App\Enums\UseCase;

enum RegulatoryScope: string
{
    case GDPR = 'GDPR';
    case CCPA = 'CCPA';
    case HIPAA = 'HIPAA';
    case SOX = 'SOX';
    case AI_ACT = 'AI_ACT';
    case FINRA = 'FINRA';
    case FDA = 'FDA';
    case PCI_DSS = 'PCI_DSS';
}
