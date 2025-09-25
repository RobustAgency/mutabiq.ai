<?php

namespace App\Enums;

enum PrimaryCategory: string
{
    case TRADITIONAL_ML = 'traditional_ml';
    case DEEP_LEARNING = 'deep_learning';
    case GENERATIVE_AI = 'generative_ai';
    case AI_AGENTS = 'ai_agents';
    case SPECIALIZED_AI = 'specialized_ai';
    case FOUNDATION_MODELS = 'foundation_models';
    case MULTIMODAL_AI = 'multimodal_ai';
}
