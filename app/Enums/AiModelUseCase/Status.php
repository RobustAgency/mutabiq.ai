<?php

namespace App\Enums\AiModelUseCase;

enum Status: string
{
    case DRAFT = 'draft';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case IN_DEVELOPMENT = 'in_development';
    case TESTING = 'testing';
    case STAGING = 'staging';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case DEPRECATED = 'deprecated';
}
