<?php

namespace App\Enums\Dataset;

enum Purpose: string
{
    case AI_ML_TRAINING = 'ai_ml_training';
    case AI_ML_FINE_TUNING = 'ai_ml_fine_tuning';
    case AI_ML_RETRIEVAL = 'ai_ml_retrieval';
    case AI_ML_EVALUATION = 'ai_ml_evaluation';
    case ANALYTIC_BUSINESS_INTELLIGENCE = 'analytic_business_intelligence';
    case OPERATIONAL_TRANSFORMATION = 'operational_transformation';
    case MASTER_DATA = 'master_data';
    case REFERENCE_DATA = 'reference_data';
    case REPORTING = 'reporting';
    case COMPLIANCE_AUDIT = 'compliance_audit';
    case ARCHIVAL_HISTORICAL = 'archival_historical';
}
