<?php

namespace App\Enums;

enum WorkflowStage: string
{
    case CREATION = 'creation';
    case TECHNICAL_REVIEW = 'technical_review';
    case ETHICS_REVIEW = 'ethics_review';
}
