<?php

namespace App\Enums;

enum PrimaryCategory: string
{
    case TRADITIONAL_ML = 'traditional_ml';
    case STATISTICAL_CLASSICAL_MODEL = 'statistical_classical_model';
    case RULE_BASED_EXPERT_SYSTEM = 'rule_based_expert_system';
    case HYBRID_RULES_ML = 'hybrid_rules_ml';
    case GENERATIVE_AI_FOUNDATION_LLM = 'generative_ai_foundation_llm';
    case GENERATIVE_AI_FINE_TUNED_DOMAIN_MODEL = 'generative_ai_fine_tuned_domain_model';
    case GENERATIVE_AI_MULTIMODAL = 'generative_ai_multimodal';
    case AGENTIC_AI_AGENT = 'agentic_ai_agent';
    case AUTONOMOUS_DECISION_SYSTEM = 'autonomous_decision_system';
    case OTHER = 'other';
}
