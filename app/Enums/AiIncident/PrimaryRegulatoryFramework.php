<?php

namespace App\Enums\AiIncident;

enum PrimaryRegulatoryFramework: string
{
    case GDPR = 'gdpr';
    case UAE_PDPL = 'uae_pdpl';
    case EU_AI_ACT = 'eu_ai_act';
    case CCPA_CPRA = 'ccpa_cpra';
    case HIPAA = 'hipaa';
    case SOX = 'sox';
    case PCI_DSS = 'pci_dss';
    case ISO_27001 = 'iso_27001';
    case MULTIPLE = 'multiple';
    case OTHER = 'other';
    case NA = 'na';
}
