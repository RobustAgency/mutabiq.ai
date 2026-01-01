<?php

namespace App\Enums\AiIncident;

enum IncidentType: string
{
    case AI_MODEL_FAILURE = 'ai_model_failure';
    case AI_BIAS_FAIRNESS_ISSUE = 'ai_bias_fairness_issue';
    case AI_SAFETY_VIOLATION = 'ai_safety_violation';
    case AI_HALLUCINATION_MISINFORMATION = 'ai_hallucination_misinformation';
    case DATA_BREACH = 'data_breach';
    case PRIVACY_VIOLATION = 'privacy_violation';
    case CONSENT_VIOLATION = 'consent_violation';
    case DATA_QUALITY_ISSUE = 'data_quality_issue';
    case UNAUTHORIZED_ACCESS = 'unauthorized_access';
    case DATA_LOSS = 'data_loss';
    case CROSS_BORDER_TRANSFER_VIOLATION = 'cross_border_transfer_violation';
    case REGULATORY_NON_COMPLIANCE = 'regulatory_non_compliance';
    case SYSTEM_OUTAGE = 'system_outage';
    case PERFORMANCE_DEGRADATION = 'performance_degradation';
    case SECURITY_INCIDENT = 'security_incident';
    case THIRD_PARTY_VENDOR_ISSUE = 'third_party_vendor_issue';
    case OTHER = 'other';
}
