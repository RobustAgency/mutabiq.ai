<?php

namespace App\Enums\AiModelDataset;

enum CreatedBy: string
{
    case DATA_ENGINEERING_TEAM = 'data_engineering_team';
    case ML_PLATFORM_TEAM = 'ml_platform_team';
    case PRIVACY_OFFICE = 'privacy_office';
    case AI_GOVERNANCE_BOARD = 'ai_governance_board';
}
