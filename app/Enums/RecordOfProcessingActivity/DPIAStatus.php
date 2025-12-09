<?php

namespace App\Enums\RecordOfProcessingActivity;

enum DPIAStatus: string
{
    case REQUIRED = 'required';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
