<?php

namespace App\Enums\Agreement;

enum SubProcessingRights: string
{
    case PROHIBITED = 'prohibited';
    case ALLOWED_WITH_NOTIFICATION = 'allowed_with_notification';
    case ALLOWED_WITH_APPROVAL = 'allowed_with_approval';
    case PER_SUB_PROCESSOR_LIST = 'per_sub_processor_list';
}
