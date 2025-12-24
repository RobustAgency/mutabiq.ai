<?php

namespace App\Enums\Vendor;

enum DataProcessingRole: string
{
    case CONTROLLER = 'controller';
    case PROCESSOR = 'processor';
    case SUB_PROCESSOR = 'sub_processor';
    case NOT_APPLICABLE = 'not_applicable';
}
