<?php

namespace App\Enums\CorrectivePreventiveAction;

enum OwnerTeam: string
{
    case PRODUCT_OPS = 'product_ops';
    case ENGINEERING = 'engineering';
    case DATA_SCIENCE = 'data_science';
    case SECURITY = 'security';
    case PRIVACY = 'privacy';
    case RISK = 'risk';
    case LEGAL = 'legal';
    case VENDOR_MGMT = 'vendor_mgmt';
}
