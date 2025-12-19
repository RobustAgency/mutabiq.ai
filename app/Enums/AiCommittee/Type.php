<?php

namespace App\Enums\AiCommittee;

enum Type: string
{
    case GOVERNANCE = 'governance';
    case ETHICS = 'ethics';
    case RISK = 'risk';
    case SECURITY = 'security';
    case PRIVACY = 'privacy';
    case PRODUCT_OPS = 'product_ops';
    case OTHER = 'other';
}
