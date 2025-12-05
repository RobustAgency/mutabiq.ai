<?php

namespace App\Enums\KriIndicator;

enum AlertRouting: string
{
    case RISK_TEAM = 'risk_team';
    case PRODUCT_OPS = 'product_ops';
    case SECURITY_IR = 'security_ir';
    case PRIVACY_OFFICE = 'privacy_office';
    case MODEL_OWNER = 'model_owner';
    case ON_CALL = 'on_call';
}
