<?php

namespace App\Enums\Vendor;

enum VendorStatus: string
{
    case EVALUATING = 'evaluating';
    case APPROVED = 'approved';
    case CONDITIONALLY_APPROVED = 'conditionally_approved';
    case RESTRICTED = 'restricted';
    case SUSPENDED = 'suspended';
    case TERMINATED = 'terminated';
}
