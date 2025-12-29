<?php

namespace App\Enums\Dataset;

enum CrossBorderTransfer: string
{
    case NONE = 'none';
    case ADEQUACY_DECISION = 'adequacy_decision';
    case STANDARD_CONTRACTUAL_CLAUSES = 'standard_contractual_clauses';
    case BINDING_CORPORATE_RULES = 'binding_corporate_rules';
    case EXPLICIT_CONSENT_FOR_TRANSFER = 'explicit_consent_for_transfer';
    case DEROGATION = 'derogation';
}
